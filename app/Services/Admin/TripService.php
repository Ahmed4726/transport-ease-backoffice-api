<?php

namespace App\Services\Admin;

use App\Models\DriverTrip;
use App\Models\DriverTripStop;
use App\Models\TripFare;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function getTrips(array $filters = []): LengthAwarePaginator
    {
        return DriverTrip::query()
            ->with(['driver.user', 'vehicle', 'route', 'stops.stop.city'])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('driver.user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('vehicle', function ($q) use ($search) {
                    $q->where('registration_number', 'LIKE', "%{$search}%")
                      ->orWhere('brand', 'LIKE', "%{$search}%")
                      ->orWhere('model', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getTrip(int $id): DriverTrip
    {
        return DriverTrip::with([
                'driver.user',
                'vehicle',
                'route',
                'stops' => function ($query) {
                    $query->orderBy('stop_order');
                },
                'stops.stop.city',
                'locations',
            ])
            ->findOrFail($id);
    }

    public function createTrip(array $data, int $adminId): DriverTrip
    {
        return DB::transaction(function () use ($data) {
            $trip = DriverTrip::create([
                'driver_id' => $data['driver_id'],
                'vehicle_id' => $data['vehicle_id'],
                'route_id' => $data['route_id'] ?? null,
                'trip_date' => $data['trip_date'] ?? now()->toDateString(),
                'departure_time' => $data['departure_time'] ?? '00:00:00',
                'total_seats' => $data['total_seats'],
                'available_seats' => $data['available_seats'] ?? $data['total_seats'],
                'is_instant' => $data['is_instant'] ?? true,
                'status' => $data['status'] ?? 'started',
            ]);

            $this->syncTripStopsAndFares($trip, $data);

            return $trip->refresh();
        });
    }

    public function updateTrip(DriverTrip $trip, array $data): DriverTrip
    {
        return DB::transaction(function () use ($trip, $data) {
            $trip->update([
                'driver_id' => $data['driver_id'],
                'vehicle_id' => $data['vehicle_id'],
                'route_id' => $data['route_id'] ?? null,
                'trip_date' => $data['trip_date'] ?? now()->toDateString(),
                'departure_time' => $data['departure_time'] ?? '00:00:00',
                'total_seats' => $data['total_seats'],
                'available_seats' => $data['available_seats'],
                'is_instant' => $data['is_instant'] ?? true,
                'status' => $data['status'] ?? 'started',
            ]);

            if (isset($data['from_stop_id'], $data['to_stop_id'])) {
                $this->syncTripStopsAndFares($trip, $data);
            }

            return $trip->refresh();
        });
    }

    protected function syncTripStopsAndFares(DriverTrip $trip, array $data): void
    {
        $fromStopId = $data['from_stop_id'] ?? null;
        $toStopId = $data['to_stop_id'] ?? null;
        $selectedStopIds = $data['selected_stop_ids'] ?? [];
        if (!is_array($selectedStopIds)) {
            $selectedStopIds = is_string($selectedStopIds) && trim($selectedStopIds) !== ''
                ? array_values(array_filter(array_map('intval', preg_split('/[\s,]+/', trim($selectedStopIds)) ?: [])))
                : [];
        }

        if (!$fromStopId || !$toStopId) {
            return;
        }

        $selectedStopIds = $this->filterIntermediateStopIds($selectedStopIds, $fromStopId, $toStopId);

        $sequence = [$fromStopId];
        foreach ($selectedStopIds as $stopId) {
            $sequence[] = $stopId;
        }
        $sequence[] = $toStopId;
        $sequence = array_values(array_unique($sequence));

        if (count($sequence) < 2) {
            return;
        }

        $trip->stops()->delete();
        $trip->fares()->delete();

        for ($index = 0; $index < count($sequence) - 1; $index++) {
            TripFare::create([
                'driver_trip_id' => $trip->id,
                'from_stop_id' => $sequence[$index],
                'to_stop_id' => $sequence[$index + 1],
                'fare' => 0,
            ]);
        }

        foreach ($sequence as $index => $stopId) {
            DriverTripStop::create([
                'driver_trip_id' => $trip->id,
                'route_stop_id' => $stopId,
                'stop_order' => $index + 1,
            ]);
        }
    }

    protected function filterIntermediateStopIds(array $selectedStopIds, int $fromStopId, int $toStopId): array
    {
        if (empty($selectedStopIds)) {
            return [];
        }

        $fromStop = \App\Models\CityStop::find($fromStopId);
        $toStop = \App\Models\CityStop::find($toStopId);

        if (!$fromStop || !$toStop) {
            return [];
        }

        $fromCityId = (int) $fromStop->city_id;
        $toCityId = (int) $toStop->city_id;

        $selectedStopIds = array_values(array_unique(array_map('intval', $selectedStopIds)));

        return \App\Models\CityStop::whereIn('id', $selectedStopIds)
            ->get()
            ->filter(function ($stop) use ($fromStopId, $toStopId, $fromCityId, $toCityId) {
                if ((int) $stop->id === (int) $fromStopId || (int) $stop->id === (int) $toStopId) {
                    return false;
                }

                $sameCityAsFrom = (int) $stop->city_id === $fromCityId;
                $sameCityAsTo = (int) $stop->city_id === $toCityId;

                if ($sameCityAsFrom || $sameCityAsTo) {
                    return true;
                }

                return true;
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function deleteTrip(DriverTrip $trip): void
    {
        $trip->delete();
    }
}
