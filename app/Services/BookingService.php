<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VehicleStatus;
use App\Models\DriverTrip;
use App\Models\Passenger;
use App\Models\TripBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private const ACTIVE_STATUSES = [BookingStatus::CONFIRMED, BookingStatus::BOARDED];

    public function availableSeats(DriverTrip $trip): int
    {
        $reservedSeats = (int) $trip->bookings()
            ->whereIn('status', array_map(fn (BookingStatus $status) => $status->value, self::ACTIVE_STATUSES))
            ->sum('seats');

        return max(0, (int) $trip->total_seats - $reservedSeats);
    }

    public function reservedSeats(DriverTrip $trip): int
    {
        return max(0, (int) $trip->total_seats - $this->availableSeats($trip));
    }

    public function canBook(DriverTrip $trip, ?int $fromStopId = null): bool
    {
        if ($trip->status === 'scheduled') {
            return true;
        }

        if ($trip->status !== 'started') {
            return false;
        }

        $stops = $trip->stops()->orderBy('stop_order')->get();
        $pickupIndex = $fromStopId === null
            ? 0
            : $stops->search(
                fn ($tripStop) => (int) $tripStop->route_stop_id === $fromStopId,
            );

        if ($pickupIndex === false) {
            return false;
        }

        $latestLocation = $trip->locations()->latest('recorded_at')->first();
        if (!$latestLocation) {
            return true;
        }

        $closestIndex = 0;
        $closestDistance = null;
        foreach ($stops as $index => $tripStop) {
            $stop = $tripStop->stop;
            if (!$stop || $stop->latitude === null || $stop->longitude === null) {
                continue;
            }

            $distance = $this->distanceKm(
                (float) $latestLocation->latitude,
                (float) $latestLocation->longitude,
                (float) $stop->latitude,
                (float) $stop->longitude,
            );
            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
                $closestIndex = $index;
            }
        }

        return $closestIndex <= $pickupIndex;
    }

    public function create(Passenger $passenger, int $tripId, int $seats, ?int $fromStopId = null, ?int $toStopId = null): TripBooking
    {
        if ($seats < 1 || $seats > 4) {
            throw ValidationException::withMessages(['seats' => 'Seats must be between 1 and 4.']);
        }

        return DB::transaction(function () use ($passenger, $tripId, $seats, $fromStopId, $toStopId): TripBooking {
            $trip = DriverTrip::query()->lockForUpdate()->find($tripId);

            if (!$trip) {
                throw ValidationException::withMessages(['trip_id' => 'The selected trip does not exist.']);
            }

            if (!$this->canBook($trip, $fromStopId)) {
                throw ValidationException::withMessages(['trip_id' => 'This trip is not available for booking.']);
            }

            $orderedStops = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->map(fn ($id) => (int) $id)->values();
            $fromStopId ??= $orderedStops->first();
            $toStopId ??= $orderedStops->last();
            $stopSequence = $orderedStops->all();
            $fromIndex = array_search((int) $fromStopId, $stopSequence, true);
            $toIndex = array_search((int) $toStopId, $stopSequence, true);

            if ($fromStopId === null || $toStopId === null || $fromStopId === $toStopId || $fromIndex === false || $toIndex === false || $fromIndex >= $toIndex) {
                throw ValidationException::withMessages(['stops' => 'The selected stops are not a valid journey for this trip.']);
            }

            $farePerSeat = 0.0;
            $tripFares = $trip->fares()->get()->keyBy(fn ($fare) => $fare->from_stop_id.'-'.$fare->to_stop_id);
            for ($index = $fromIndex; $index < $toIndex; $index++) {
                $from = $orderedStops[$index];
                $to = $orderedStops[$index + 1];
                $fare = $tripFares->get($from.'-'.$to);
                if (!$fare || (float) $fare->fare <= 0) {
                    throw ValidationException::withMessages(['fare' => 'A fare is not configured for the selected journey.']);
                }
                $farePerSeat += (float) $fare->fare;
            }

            $totalFare = round($farePerSeat * $seats, 2);

            $tripDriver = $trip->driver?->user;
            if (!$tripDriver || $tripDriver->role !== UserRole::DRIVER || $tripDriver->status !== UserStatus::APPROVED || $trip->vehicle?->status !== VehicleStatus::APPROVED) {
                throw ValidationException::withMessages(['trip_id' => 'This trip is not available for booking.']);
            }

            $hasActiveBooking = $trip->bookings()
                ->where('passenger_id', $passenger->id)
                ->whereIn('status', array_map(fn (BookingStatus $status) => $status->value, self::ACTIVE_STATUSES))
                ->exists();

            if ($hasActiveBooking) {
                throw ValidationException::withMessages(['trip_id' => 'You already have an active booking for this trip.']);
            }

            if ($this->availableSeats($trip) < $seats) {
                throw ValidationException::withMessages(['seats' => 'Not enough seats are available.']);
            }

            $booking = TripBooking::create([
                'driver_trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'from_stop_id' => $fromStopId,
                'to_stop_id' => $toStopId,
                'seats' => $seats,
                'fare_per_seat' => number_format($farePerSeat, 2, '.', ''),
                'total_fare' => number_format($totalFare, 2, '.', ''),
                'status' => BookingStatus::CONFIRMED,
                'booking_reference' => $this->newReference(),
            ]);

            // Keep the legacy field synchronized for existing consumers. Availability remains authoritative from bookings.
            $trip->update(['available_seats' => $this->availableSeats($trip)]);

            return $booking->load('trip.stops.stop.city');
        }, 3);
    }

    public function cancel(TripBooking $booking, ?string $reason = null): TripBooking
    {
        return DB::transaction(function () use ($booking, $reason): TripBooking {
            $booking = TripBooking::query()->lockForUpdate()->findOrFail($booking->id);
            $trip = DriverTrip::query()->lockForUpdate()->findOrFail($booking->driver_trip_id);

            if ($booking->status !== BookingStatus::CONFIRMED || $trip->status !== 'scheduled') {
                throw ValidationException::withMessages(['booking' => 'This booking cannot be cancelled.']);
            }

            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $trip->update(['available_seats' => $this->availableSeats($trip)]);

            return $booking->fresh('trip.stops.stop.city');
        }, 3);
    }

    public function board(DriverTrip $trip, TripBooking $booking): TripBooking
    {
        return DB::transaction(function () use ($trip, $booking): TripBooking {
            $trip = DriverTrip::query()->lockForUpdate()->findOrFail($trip->id);
            $booking = TripBooking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->driver_trip_id !== $trip->id) {
                throw ValidationException::withMessages(['booking' => 'This booking does not belong to the selected trip.']);
            }

            if ($trip->status !== 'started') {
                throw ValidationException::withMessages(['trip' => 'This trip is not currently accepting boarding.']);
            }

            if ($booking->status !== BookingStatus::CONFIRMED) {
                throw ValidationException::withMessages(['booking' => 'Only confirmed bookings can be boarded.']);
            }

            $booking->update([
                'status' => BookingStatus::BOARDED,
                'boarded_at' => now(),
            ]);

            return $booking->fresh(['passenger.user', 'trip']);
        }, 3);
    }

    public function markNoShow(DriverTrip $trip, TripBooking $booking): TripBooking
    {
        return DB::transaction(function () use ($trip, $booking): TripBooking {
            $trip = DriverTrip::query()->lockForUpdate()->findOrFail($trip->id);
            $booking = TripBooking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->driver_trip_id !== $trip->id) {
                throw ValidationException::withMessages(['booking' => 'This booking does not belong to the selected trip.']);
            }

            if ($trip->status !== 'started') {
                throw ValidationException::withMessages(['trip' => 'This trip is not currently accepting no-show updates.']);
            }

            if ($booking->status !== BookingStatus::CONFIRMED) {
                throw ValidationException::withMessages(['booking' => 'Only confirmed bookings can be marked as no-show.']);
            }

            $booking->update([
                'status' => BookingStatus::NO_SHOW,
                'no_show_at' => now(),
            ]);

            $trip->update(['available_seats' => $this->availableSeats($trip)]);

            return $booking->fresh(['passenger.user', 'trip']);
        }, 3);
    }

    public function completeTrip(DriverTrip $trip): DriverTrip
    {
        return DB::transaction(function () use ($trip): DriverTrip {
            $trip = DriverTrip::query()->lockForUpdate()->findOrFail($trip->id);

            if ($trip->status !== 'started') {
                throw ValidationException::withMessages(['trip' => 'Only started trips can be completed.']);
            }

            $completedAt = now();
            $bookings = $trip->bookings()->lockForUpdate()->get();
            foreach ($bookings as $booking) {
                if ($booking->status === BookingStatus::BOARDED) {
                    $booking->update([
                        'status' => BookingStatus::COMPLETED,
                        'completed_at' => $completedAt,
                    ]);
                } elseif ($booking->status === BookingStatus::CONFIRMED) {
                    $booking->update([
                        'status' => BookingStatus::NO_SHOW,
                        'no_show_at' => $completedAt,
                    ]);
                }
            }

            $trip->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'available_seats' => $this->availableSeats($trip),
            ]);

            return $trip->fresh();
        }, 3);
    }

    private function newReference(): string
    {
        do {
            $reference = 'TE-'.strtoupper(bin2hex(random_bytes(4)));
        } while (TripBooking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function distanceKm(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $earthRadiusKm = 6371.0;
        $lat1 = deg2rad($fromLatitude);
        $lon1 = deg2rad($fromLongitude);
        $lat2 = deg2rad($toLatitude);
        $lon2 = deg2rad($toLongitude);
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;
        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
