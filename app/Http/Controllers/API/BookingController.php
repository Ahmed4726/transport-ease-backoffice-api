<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\DriverTrip;
use App\Models\TripBooking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BookingService $bookingService) {}

    public function store(StoreBookingRequest $request)
    {
        $passenger = $this->approvedPassenger($request);
        if (!$passenger) {
            return $this->error('Unauthorized.', [], 403);
        }

        $booking = $this->bookingService->create(
            $passenger,
            $request->integer('trip_id'),
            $request->integer('seats'),
            $request->integer('from_stop_id'),
            $request->integer('to_stop_id'),
        );

        return $this->success('Booking confirmed successfully.', new BookingResource($booking), 201);
    }

    public function index(Request $request)
    {
        $passenger = $this->approvedPassenger($request);
        if (!$passenger) {
            return $this->error('Unauthorized.', [], 403);
        }

        $validated = $request->validate(['status' => ['nullable', 'string', 'in:confirmed,cancelled,boarded,completed,no_show']]);
        $bookings = TripBooking::query()
            ->where('passenger_id', $passenger->id)
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->with('trip.stops.stop.city')
            ->latest()
            ->get();

        return $this->success('Bookings fetched successfully.', BookingResource::collection($bookings));
    }

    public function show(Request $request, TripBooking $booking)
    {
        $passenger = $this->approvedPassenger($request);
        if (!$passenger || $booking->passenger_id !== $passenger->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        return $this->success('Booking fetched successfully.', new BookingResource($booking->load('trip.stops.stop.city')));
    }

    public function cancel(Request $request, TripBooking $booking)
    {
        $passenger = $this->approvedPassenger($request);
        if (!$passenger || $booking->passenger_id !== $passenger->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        $validated = $request->validate(['cancellation_reason' => ['nullable', 'string', 'max:1000']]);
        $booking = $this->bookingService->cancel($booking, $validated['cancellation_reason'] ?? null);

        return $this->success('Booking cancelled successfully.', new BookingResource($booking));
    }

    public function driverBookings(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::DRIVER || $user->status !== UserStatus::APPROVED || !$user->driver || $driverTrip->driver_id !== $user->driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        $bookings = $driverTrip->bookings()
            ->with('passenger.user')
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 1 WHEN 'boarded' THEN 2 WHEN 'cancelled' THEN 3 WHEN 'completed' THEN 4 WHEN 'no_show' THEN 5 ELSE 6 END")
            ->orderBy('booking_reference')
            ->get();

        return $this->success('Trip bookings fetched successfully.', [
            'trip' => [
                'id' => $driverTrip->id,
                'status' => $driverTrip->status,
                'trip_date' => $driverTrip->trip_date?->toDateString(),
                'departure_time' => $driverTrip->departure_time?->format('H:i'),
                'total_capacity' => (int) $driverTrip->total_seats,
                'booked_seats' => $this->bookingService->reservedSeats($driverTrip),
                'available_seats' => $this->bookingService->availableSeats($driverTrip),
                'vehicle_name' => $driverTrip->vehicle?->plate_number,
            ],
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function board(Request $request, DriverTrip $driverTrip, TripBooking $booking)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::DRIVER || $user->status !== UserStatus::APPROVED || !$user->driver || $driverTrip->driver_id !== $user->driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        try {
            $booking = $this->bookingService->board($driverTrip, $booking);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return $this->error($exception->validator->errors()->first(), $exception->errors(), 422);
        }

        return $this->success('Passenger boarded successfully.', new BookingResource($booking->load('passenger.user')));
    }

    public function noShow(Request $request, DriverTrip $driverTrip, TripBooking $booking)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::DRIVER || $user->status !== UserStatus::APPROVED || !$user->driver || $driverTrip->driver_id !== $user->driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        try {
            $booking = $this->bookingService->markNoShow($driverTrip, $booking);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return $this->error($exception->validator->errors()->first(), $exception->errors(), 422);
        }

        return $this->success('Passenger marked as no-show.', new BookingResource($booking->load('passenger.user')));
    }

    private function approvedPassenger(Request $request)
    {
        $user = $request->user();

        return $user && $user->role === UserRole::PASSENGER && $user->status === UserStatus::APPROVED
            ? $user->passenger
            : null;
    }
}
