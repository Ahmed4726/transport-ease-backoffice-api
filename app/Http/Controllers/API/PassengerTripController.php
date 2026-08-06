<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverTrip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PassengerTripController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->passenger) {
            return $this->error('Passenger profile not found.', [], 404);
        }

        $validated = $request->validate([
            'from_stop_id' => ['nullable', 'integer', 'exists:city_stops,id'],
            'to_stop_id' => ['nullable', 'integer', 'exists:city_stops,id'],
        ]);

        $query = DriverTrip::query()
            ->whereIn('status', ['scheduled', 'started'])
            ->with(['driver.user', 'vehicle', 'stops.stop.city'])
            ->orderBy('trip_date')
            ->orderBy('departure_time');

        $trips = $query->get();

        $fromStopId = $validated['from_stop_id'] ?? null;
        $toStopId = $validated['to_stop_id'] ?? null;

        $payload = $trips->filter(function (DriverTrip $trip) use ($fromStopId, $toStopId): bool {
            $stopIds = $trip->stops->pluck('route_stop_id')->filter()->values()->all();

            if ($fromStopId && !in_array($fromStopId, $stopIds, true)) {
                return false;
            }

            if ($toStopId && !in_array($toStopId, $stopIds, true)) {
                return false;
            }

            if ($fromStopId && $toStopId && $fromStopId === $toStopId) {
                return false;
            }

            return true;
        })->map(function (DriverTrip $trip) use ($fromStopId, $toStopId): array {
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

            $progress = $this->buildTripProgress($trip, $stopData);

            return [
                'id' => $trip->id,
                'driver_name' => $trip->driver?->user?->name ?? 'Driver',
                'driver_phone' => $trip->driver?->user?->phone ?? null,
                'vehicle_name' => $trip->vehicle?->plate_number ?? 'Vehicle',
                'status' => $trip->status,
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
            ];
        })->values();

        return $this->success('Available trips fetched successfully.', $payload);
    }

    private function buildTripProgress(DriverTrip $trip, array $stops): array
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
        $totalEtaMinutes = 0;

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
            $totalEtaMinutes += $segmentMinutes;
        }

        $hasStarted = $trip->status === 'started' && $trip->started_at;
        $startedAt = $hasStarted ? Carbon::parse($trip->started_at) : null;
        $elapsedMinutes = $startedAt ? max(0, (int) $startedAt->diffInMinutes(Carbon::now())) : 0;

        if (!$hasStarted || $elapsedMinutes <= 0) {
            return [
                'current_stop' => $stops[0],
                'next_stop' => $stops[1] ?? null,
                'current_location' => $stops[0],
                'eta_to_next_stop' => $segments[0]['minutes'] ?? 0,
                'progress_percent' => 0,
                'total_eta_minutes' => $totalEtaMinutes,
            ];
        }

        $accumulated = 0;
        $currentSegmentIndex = 0;

        foreach ($segments as $index => $segment) {
            $segmentMinutes = max(1, $segment['minutes']);
            if ($elapsedMinutes <= $accumulated + $segmentMinutes) {
                $currentSegmentIndex = $index;
                break;
            }
            $accumulated += $segmentMinutes;
            $currentSegmentIndex = $index + 1;
        }

        if ($currentSegmentIndex >= count($segments)) {
            return [
                'current_stop' => $stops[count($stops) - 1],
                'next_stop' => null,
                'current_location' => $stops[count($stops) - 1],
                'eta_to_next_stop' => 0,
                'progress_percent' => 100,
                'total_eta_minutes' => $totalEtaMinutes,
            ];
        }

        $segment = $segments[$currentSegmentIndex];
        $segmentElapsed = max(0, $elapsedMinutes - $accumulated);
        $segmentProgress = $segment['minutes'] <= 0 ? 1.0 : min(1.0, $segmentElapsed / max(1, $segment['minutes']));

        $from = $segment['from'];
        $to = $segment['to'];
        $currentLocation = [
            'id' => $to['id'],
            'display_name' => $to['display_name'],
            'city_name' => $to['city_name'],
            'latitude' => ($from['latitude'] ?? 0) + (($to['latitude'] ?? 0) - ($from['latitude'] ?? 0)) * $segmentProgress,
            'longitude' => ($from['longitude'] ?? 0) + (($to['longitude'] ?? 0) - ($from['longitude'] ?? 0)) * $segmentProgress,
        ];

        $remainingInSegment = max(0, max(1, $segment['minutes']) - $segmentElapsed);
        $remainingMinutes = $remainingInSegment;

        for ($i = $currentSegmentIndex + 1; $i < count($segments); $i++) {
            $remainingMinutes += $segments[$i]['minutes'];
        }

        return [
            'current_stop' => $segment['from'],
            'next_stop' => $segment['to'],
            'current_location' => $currentLocation,
            'eta_to_next_stop' => max(1, $remainingInSegment),
            'progress_percent' => $totalEtaMinutes <= 0 ? 0 : min(100, (int) round(($elapsedMinutes / max(1, $totalEtaMinutes)) * 100)),
            'total_eta_minutes' => $totalEtaMinutes,
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
