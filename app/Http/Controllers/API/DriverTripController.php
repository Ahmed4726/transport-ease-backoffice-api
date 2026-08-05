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
                $tripData['stop_count'] = $orderedStops->count();

                return $tripData;
            });

        return $this->success('Trips fetched successfully.', $trips);
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
