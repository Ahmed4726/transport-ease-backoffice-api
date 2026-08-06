<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverTrip;
use App\Models\TripFare;
use App\Models\DriverTripStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverTripController extends Controller
{
    use \App\Traits\ApiResponse;

    protected function validateSeatCount(int $requestedSeats, ?\App\Models\Vehicle $vehicle): array
    {
        if ($requestedSeats < 1) {
            return [
                'valid' => false,
                'message' => 'Seats must be at least 1.',
            ];
        }

        $maxSeats = 1;
        if ($vehicle) {
            $maxSeats = max(1, $vehicle->available_seats > 0 ? $vehicle->available_seats : 1);
        }

        if ($vehicle?->status === \App\Enums\VehicleStatus::APPROVED) {
            if ($requestedSeats > $maxSeats) {
                return [
                    'valid' => false,
                    'message' => 'Seats cannot be greater than the approved vehicle capacity (' . $maxSeats . ').',
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Seats are valid.',
        ];
    }

    public function show(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        if ($driverTrip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        $trip = $driverTrip->load(['stops' => function ($query) {
            $query->orderBy('stop_order');
        }, 'stops.stop.city']);

        $orderedStops = $trip->stops->sortBy('stop_order')->values();
        $tripData = $trip->toArray();
        $tripData['stops'] = $orderedStops->map(function ($tripStop) {
            $stop = $tripStop->stop;
            return [
                'id' => $tripStop->route_stop_id,
                'display_name' => $stop?->location_name ?? $stop?->address ?? null,
                'latitude' => $stop?->latitude,
                'longitude' => $stop?->longitude,
                'city_name' => $stop?->city?->name ?? null,
                'stop_order' => $tripStop->stop_order,
            ];
        })->values();

        $tripData['from_city_name'] = $orderedStops->first()?->stop?->city?->name ?? null;
        $tripData['to_city_name'] = $orderedStops->last()?->stop?->city?->name ?? null;
        $tripData['from_city_id'] = $orderedStops->first()?->stop?->city?->id ?? null;
        $tripData['to_city_id'] = $orderedStops->last()?->stop?->city?->id ?? null;
        $tripData['stop_count'] = $orderedStops->count();

        return $this->success('Trip details fetched successfully.', $tripData);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $trips = DriverTrip::where('driver_id', $driver->id)
            ->with(['stops' => function ($query) {
                $query->orderBy('stop_order');
            }, 'stops.stop.city'])
            ->get()
            ->map(function ($trip) {
                $orderedStops = $trip->stops->sortBy('stop_order')->values();
                $firstStop = $orderedStops->first()?->stop;
                $lastStop = $orderedStops->last()?->stop;

                $tripData = $trip->toArray();
                $tripData['from_city_name'] = $firstStop?->city?->name ?? $firstStop?->name ?? null;
                $tripData['to_city_name'] = $lastStop?->city?->name ?? $lastStop?->name ?? null;
                $tripData['from_city_id'] = $firstStop?->city?->id ?? null;
                $tripData['to_city_id'] = $lastStop?->city?->id ?? null;
                $tripData['stop_count'] = $orderedStops->count();

                return $tripData;
            });

        return $this->success('Trips fetched successfully.', $trips);
    }

    public function update(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        if ($driverTrip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'total_seats' => ['nullable', 'integer', 'min:1'],
            'stop_ids' => ['nullable', 'array'],
            'stop_ids.*' => ['integer', 'exists:city_stops,id'],
        ]);

        if (array_key_exists('total_seats', $validated)) {
            $seatValidation = $this->validateSeatCount($validated['total_seats'], $driverTrip->vehicle);
            if (!$seatValidation['valid']) {
                return $this->error($seatValidation['message'], [], 422);
            }
        }

        DB::beginTransaction();

        try {
            if (array_key_exists('total_seats', $validated)) {
                $driverTrip->total_seats = $validated['total_seats'];
                $driverTrip->available_seats = max(1, min($validated['total_seats'], $driverTrip->available_seats ?? $validated['total_seats']));
                $driverTrip->save();
            }

            if (array_key_exists('stop_ids', $validated)) {
                $sequence = array_values(array_unique($validated['stop_ids']));
                if (count($sequence) < 2) {
                    return $this->error('At least two stops are required.', [], 422);
                }

                $driverTrip->stops()->delete();
                $driverTrip->fares()->delete();

                for ($i = 0; $i < count($sequence) - 1; $i++) {
                    TripFare::create([
                        'driver_trip_id' => $driverTrip->id,
                        'from_stop_id' => $sequence[$i],
                        'to_stop_id' => $sequence[$i + 1],
                        'fare' => 0,
                    ]);
                }

                foreach ($sequence as $index => $stopId) {
                    DriverTripStop::create([
                        'driver_trip_id' => $driverTrip->id,
                        'route_stop_id' => $stopId,
                        'stop_order' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return $this->success('Trip updated successfully.', null, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error('Failed to update trip: ' . $e->getMessage(), [], 500);
        }
    }

    public function destroy(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        if ($driverTrip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        DB::beginTransaction();

        try {
            $driverTrip->fares()->delete();
            $driverTrip->stops()->delete();
            $driverTrip->delete();

            DB::commit();

            return $this->success('Trip deleted successfully.', null, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error('Failed to delete trip: ' . $e->getMessage(), [], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'trip_date' => ['nullable', 'date'],
            'departure_time' => ['nullable'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'from_stop_id' => ['required', 'integer', 'exists:city_stops,id'],
            'to_stop_id' => ['required', 'integer', 'exists:city_stops,id'],
            'is_instant' => ['nullable', 'boolean'],
            'selected_stop_ids' => ['nullable', 'array'],
            'selected_stop_ids.*' => ['integer', 'exists:city_stops,id'],
        ]);

        $vehicle = \App\Models\Vehicle::find($validated['vehicle_id']);
        $seatValidation = $this->validateSeatCount($validated['total_seats'], $vehicle);
        if (!$seatValidation['valid']) {
            return $this->error($seatValidation['message'], [], 422);
        }

        DB::beginTransaction();

        try {
            $trip = DriverTrip::create(array_merge($validated, [
                'driver_id' => $driver->id,
                'route_id' => null,
                'available_seats' => $validated['total_seats'],
                'is_instant' => $validated['is_instant'] ?? false,
                'status' => 'scheduled',
            ]));

            // build sequence of stops: from -> selected stops -> to
            $sequence = [$validated['from_stop_id']];
            if (!empty($validated['selected_stop_ids'])) {
                foreach ($validated['selected_stop_ids'] as $s) {
                    $sequence[] = $s;
                }
            }
            $sequence[] = $validated['to_stop_id'];

            $sequence = array_values(array_unique($sequence));

            for ($i = 0; $i < count($sequence) - 1; $i++) {
                $fFrom = $sequence[$i];
                $fTo = $sequence[$i + 1];
                TripFare::create([
                    'driver_trip_id' => $trip->id,
                    'from_stop_id' => $fFrom,
                    'to_stop_id' => $fTo,
                    'fare' => 0,
                ]);
            }

            // persist selected stops order
            $order = 1;
            foreach ($sequence as $stopId) {
                DriverTripStop::updateOrCreate([
                    'driver_trip_id' => $trip->id,
                    'route_stop_id' => $stopId,
                ], [
                    'stop_order' => $order++,
                ]);
            }

            DB::commit();

            return $this->success('Trip created successfully.', $trip, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error('Failed to create trip: ' . $e->getMessage(), [], 500);
        }
    }
}
