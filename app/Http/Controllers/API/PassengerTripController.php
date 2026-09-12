<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverTrip;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VehicleStatus;
use App\Services\BookingService;
use Illuminate\Http\Request;

class PassengerTripController extends Controller
{
    use \App\Traits\ApiResponse;

    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->passenger || $user->role !== UserRole::PASSENGER || $user->status !== UserStatus::APPROVED) {
            return $this->error('Passenger profile not found.', [], 404);
        }

        $validated = $request->validate([
            'from_stop_id' => ['nullable', 'integer', 'exists:city_stops,id'],
            'to_stop_id' => ['nullable', 'integer', 'exists:city_stops,id'],
        ]);

        $query = DriverTrip::query()
            ->whereIn('status', ['scheduled', 'started'])
            ->whereHas('driver.user', fn ($query) => $query->where('status', UserStatus::APPROVED->value))
            ->whereHas('vehicle', fn ($query) => $query->where('status', VehicleStatus::APPROVED->value))
            ->with(['driver.user', 'vehicle', 'stops.stop.city'])
            ->orderBy('trip_date')
            ->orderBy('departure_time');

        $trips = $query->get()->unique('id')->values();

        $fromStopId = isset($validated['from_stop_id']) ? (int) $validated['from_stop_id'] : null;
        $toStopId = isset($validated['to_stop_id']) ? (int) $validated['to_stop_id'] : null;

        $payload = $trips->filter(function (DriverTrip $trip) use ($fromStopId, $toStopId): bool {
            $stopIds = $trip->stops->pluck('route_stop_id')->filter()->values()->all();

            $fromIndex = $fromStopId ? array_search($fromStopId, $stopIds, true) : null;
            $toIndex = $toStopId ? array_search($toStopId, $stopIds, true) : null;

            if ($fromStopId && !in_array($fromStopId, $stopIds, true)) {
                return false;
            }

            if ($toStopId && !in_array($toStopId, $stopIds, true)) {
                return false;
            }

            if ($fromStopId && $toStopId && $fromStopId === $toStopId) {
                return false;
            }

            if ($fromIndex !== null && $toIndex !== null && $fromIndex >= $toIndex) {
                return false;
            }

            if (!in_array($trip->status, ['scheduled', 'started'], true)) {
                return false;
            }

            $targetStopId = $toStopId ?? $fromStopId;
            if ($targetStopId === null) {
                return true;
            }

            $latestLocation = $trip->locations()->latest('recorded_at')->first();
            if (!$latestLocation) {
                return true;
            }

            return $this->isDriverStillBeforePassengerStop($trip, $targetStopId);
        })->sortBy(function (DriverTrip $trip): int {
            return $trip->status === 'started' ? 0 : 1;
        })->unique(function (DriverTrip $trip) use ($fromStopId, $toStopId): string {
            return implode(':', [
                $trip->driver_id,
                $trip->vehicle_id,
                $fromStopId ?? 'first',
                $toStopId ?? 'last',
            ]);
        })->values()->map(function (DriverTrip $trip) use ($fromStopId, $toStopId): array {
            $orderedStops = $trip->stops->sortBy('stop_order')->values();
            $stopData = [];

            foreach ($orderedStops as $tripStop) {
                $stop = $tripStop->stop;
                $stopData[] = [
                    'id' => $tripStop->route_stop_id,
                    'display_name' => $stop?->location_name ?? $stop?->address ?? 'Stop',
                    'city_name' => $stop?->city?->name ?? null,
                    'latitude' => $stop?->latitude,
                    'longitude' => $stop?->longitude,
                ];
            }

            $progress = $this->buildTripProgress($trip, $stopData, $toStopId ?? $fromStopId);
            $liveDriver = $this->buildDriverLiveSnapshot($trip, $toStopId ?? $fromStopId, $stopData);

            return [
                'id' => $trip->id,
                'driver_name' => $trip->driver?->user?->name ?? 'Driver',
                'driver_phone' => $trip->driver?->user?->phone ?? null,
                'vehicle_name' => $trip->vehicle?->plate_number ?? 'Vehicle',
                'status' => $trip->status,
                'can_book' => $this->bookingService->canBook($trip, $fromStopId),
                'trip_date' => $trip->trip_date?->toDateString(),
                'departure_time' => $trip->departure_time?->format('H:i'),
                'from_stop' => $fromStopId ? $this->findStop($stopData, $fromStopId) : ($stopData[0] ?? null),
                'to_stop' => $toStopId ? $this->findStop($stopData, $toStopId) : ($stopData[count($stopData) - 1] ?? null),
                'current_stop' => $progress['current_stop'],
                'next_stop' => $progress['next_stop'],
                'current_location' => $progress['current_location'],
                'eta_to_next_stop' => $progress['eta_to_next_stop'],
                'progress_percent' => $progress['progress_percent'],
                'total_eta_minutes' => $progress['total_eta_minutes'],
                'stop_count' => count($stopData),
                'driver_location' => $liveDriver['driver_location'],
                'distance_to_passenger_stop_km' => $liveDriver['distance_to_passenger_stop_km'],
                'eta_to_passenger_stop_minutes' => $liveDriver['eta_to_passenger_stop_minutes'],
                'driver_speed_kmh' => $liveDriver['driver_speed_kmh'],
                'seats_booked' => $this->bookingService->reservedSeats($trip),
                'available_seats' => $this->bookingService->availableSeats($trip),
                'total_capacity' => (int) ($trip->total_seats ?? 0),
                'has_live_driver' => $liveDriver['has_live_driver'],
            ];
        })->values();

        return $this->success('Available trips fetched successfully.', $payload);
    }

    private function buildTripProgress(DriverTrip $trip, array $stops, ?int $targetStopId = null): array
    {
        if (count($stops) < 2) {
            return [
                'current_stop' => $stops[0] ?? null,
                'next_stop' => null,
                'current_location' => $stops[0] ?? null,
                'eta_to_next_stop' => 0,
                'progress_percent' => 0,
                'total_eta_minutes' => 0,
            ];
        }

        $segments = [];
        $totalRouteMinutes = 0;

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $from = $stops[$i];
            $to = $stops[$i + 1];
            $distanceKm = $this->distanceKm($from, $to);
            $segmentMinutes = $distanceKm <= 0 ? 0 : (int) round(($distanceKm / 65.0) * 60);
            $segmentMinutes = max(1, $segmentMinutes);
            $segments[] = [
                'from' => $from,
                'to' => $to,
                'minutes' => $segmentMinutes,
            ];
            $totalRouteMinutes += $segmentMinutes;
        }

        $targetIndex = $targetStopId === null ? count($stops) - 1 : collect($stops)->search(fn ($stop) => (int) $stop['id'] === $targetStopId);
        if ($targetIndex === false) $targetIndex = count($stops) - 1;

        $latestLocation = $trip->locations()->latest('recorded_at')->first();
        $currentIndex = 0;
        if ($latestLocation) {
            $closestDistance = null;
            foreach ($stops as $index => $stop) {
                $distance = $this->distanceKm([
                    'latitude' => (float) $latestLocation->latitude,
                    'longitude' => (float) $latestLocation->longitude,
                ], $stop);
                if ($closestDistance === null || $distance < $closestDistance) {
                    $closestDistance = $distance;
                    $currentIndex = $index;
                }
            }
        }

        $nextIndex = min($currentIndex + 1, count($stops) - 1);
        $currentStop = $stops[$currentIndex];
        $nextStop = $currentIndex < count($stops) - 1 ? $stops[$nextIndex] : null;
        $currentLocation = $latestLocation ? [
            'id' => null,
            'display_name' => 'Current location',
            'city_name' => $currentStop['city_name'],
            'latitude' => (float) $latestLocation->latitude,
            'longitude' => (float) $latestLocation->longitude,
            'recorded_at' => $latestLocation->recorded_at?->toDateTimeString(),
        ] : $currentStop;

        $etaToNext = $nextStop && $latestLocation
            ? max(1, (int) round(($this->distanceKm($currentLocation, $nextStop) / 65.0) * 60))
            : ($segments[$currentIndex]['minutes'] ?? 0);
        $totalEtaMinutes = 0;
        if ($targetIndex > $currentIndex) {
            if ($latestLocation) {
                $totalEtaMinutes += (int) round(($this->distanceKm($currentLocation, $stops[$currentIndex + 1]) / 65.0) * 60);
                for ($i = $currentIndex + 1; $i < $targetIndex; $i++) $totalEtaMinutes += $segments[$i]['minutes'];
            } else {
                for ($i = $currentIndex; $i < $targetIndex; $i++) $totalEtaMinutes += $segments[$i]['minutes'];
            }
        }

        return [
            'current_stop' => $currentStop,
            'next_stop' => $nextStop,
            'current_location' => $currentLocation,
            'eta_to_next_stop' => $etaToNext,
            'progress_percent' => $totalRouteMinutes <= 0 ? 0 : min(100, (int) round(($currentIndex / max(1, count($stops) - 1)) * 100)),
            'total_eta_minutes' => $totalEtaMinutes,
        ];
    }

    private function isDriverStillBeforePassengerStop(DriverTrip $trip, ?int $targetStopId): bool
    {
        if (!$targetStopId) {
            return true;
        }

        $stops = $trip->stops()->orderBy('stop_order')->get();
        $targetIndex = $stops->search(function ($tripStop) use ($targetStopId) {
            return (int) $tripStop->route_stop_id === (int) $targetStopId;
        });

        if ($targetIndex === false || $targetIndex === null) {
            return true;
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

            $distance = $this->distanceKm([
                'latitude' => (float) $latestLocation->latitude,
                'longitude' => (float) $latestLocation->longitude,
            ], [
                'latitude' => (float) $stop->latitude,
                'longitude' => (float) $stop->longitude,
            ]);

            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
                $closestIndex = $index;
            }
        }

        return $closestIndex <= $targetIndex;
    }

    private function buildDriverLiveSnapshot(DriverTrip $trip, ?int $targetStopId, array $stopData): array
    {
        $latestLocation = $trip->locations()->latest('recorded_at')->first();
        if (!$latestLocation) {
            return [
                'driver_location' => null,
                'distance_to_passenger_stop_km' => null,
                'eta_to_passenger_stop_minutes' => null,
                'driver_speed_kmh' => null,
                'has_live_driver' => false,
            ];
        }

        $driverLocation = [
            'latitude' => (float) $latestLocation->latitude,
            'longitude' => (float) $latestLocation->longitude,
            'recorded_at' => $latestLocation->recorded_at?->toDateTimeString(),
        ];

        $targetStop = $targetStopId ? $this->findStop($stopData, $targetStopId) : null;
        $distanceToTarget = $targetStop && isset($targetStop['latitude'], $targetStop['longitude'])
            ? $this->distanceKm($driverLocation, $targetStop)
            : null;

        $previousLocation = $trip->locations()->orderByDesc('recorded_at')->skip(1)->first();
        $driverSpeedKmh = null;
        if ($previousLocation && $previousLocation->recorded_at && $latestLocation->recorded_at) {
            $travelDistanceKm = $this->distanceKm([
                'latitude' => (float) $previousLocation->latitude,
                'longitude' => (float) $previousLocation->longitude,
            ], [
                'latitude' => (float) $latestLocation->latitude,
                'longitude' => (float) $latestLocation->longitude,
            ]);

            $elapsedMinutes = max(1, (int) $previousLocation->recorded_at->diffInMinutes($latestLocation->recorded_at, false));
            $driverSpeedKmh = $travelDistanceKm > 0 ? max(0, round(($travelDistanceKm / max(1, $elapsedMinutes)) * 60, 1)) : 0;
        }

        $etaMinutes = null;
        if ($distanceToTarget !== null && $distanceToTarget > 0 && is_numeric($driverSpeedKmh) && (float) $driverSpeedKmh > 0) {
            $etaMinutes = max(1, (int) round(($distanceToTarget / (float) $driverSpeedKmh) * 60));
        }

        return [
            'driver_location' => $driverLocation,
            'distance_to_passenger_stop_km' => $distanceToTarget !== null ? round($distanceToTarget, 1) : null,
            'eta_to_passenger_stop_minutes' => $etaMinutes,
            'driver_speed_kmh' => $driverSpeedKmh,
            'has_live_driver' => true,
        ];
    }

    private function distanceKm(array $from, array $to): float
    {
        if (!isset($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude'])) {
            return 0.0;
        }

        $earthRadiusKm = 6371.0;
        $lat1 = deg2rad((float) $from['latitude']);
        $lon1 = deg2rad((float) $from['longitude']);
        $lat2 = deg2rad((float) $to['latitude']);
        $lon2 = deg2rad((float) $to['longitude']);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function findStop(array $stops, int $stopId): ?array
    {
        foreach ($stops as $stop) {
            if (($stop['id'] ?? null) == $stopId) {
                return $stop;
            }
        }

        return null;
    }
}
