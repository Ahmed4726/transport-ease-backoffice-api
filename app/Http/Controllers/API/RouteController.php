<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityStop;
use App\Models\Route as RouteModel;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index(Request $request)
    {
        $routes = RouteModel::where('is_active', true)->get();

        return $this->success('Routes fetched successfully.', $routes);
    }

    public function stops(Request $request, RouteModel $route)
    {
        $route->load(['stops.city', 'fares']);

        return $this->success('Stops fetched successfully.', $route->stops);
    }

    public function stopsBetween(Request $request)
    {
        $validated = $request->validate([
            'from_city_id' => ['required', 'exists:cities,id'],
            'to_city_id' => ['required', 'exists:cities,id'],
        ]);

        $fromCity = City::with('cityStops.city')->findOrFail($validated['from_city_id']);
        $toCity = City::with('cityStops.city')->findOrFail($validated['to_city_id']);

        $cities = City::with('cityStops.city')
            ->whereHas('cityStops')
            ->get();

        $journeyStops = collect();

        $fromLng = (float) $fromCity->longitude;
        $fromLat = (float) $fromCity->latitude;
        $toLng = (float) $toCity->longitude;
        $toLat = (float) $toCity->latitude;

        $segmentDx = $toLng - $fromLng;
        $segmentDy = $toLat - $fromLat;
        $segmentLengthSquared = ($segmentDx * $segmentDx) + ($segmentDy * $segmentDy);

        $selectedCities = collect();

        if ($segmentLengthSquared > 0) {
            $selectedCities = $cities->filter(function (City $city) use ($fromCity, $toCity, $fromLng, $fromLat, $toLng, $toLat, $segmentDx, $segmentDy, $segmentLengthSquared) {
                if ($city->latitude === null || $city->longitude === null) {
                    return false;
                }

                $cityLng = (float) $city->longitude;
                $cityLat = (float) $city->latitude;
                $progress = (($cityLng - $fromLng) * $segmentDx + ($cityLat - $fromLat) * $segmentDy) / $segmentLengthSquared;

                if ($city->id === $fromCity->id || $city->id === $toCity->id) {
                    return $progress >= 0 && $progress <= 1;
                }

                return $progress > 0 && $progress < 1;
            })->map(function (City $city) use ($fromLng, $fromLat, $segmentDx, $segmentDy, $segmentLengthSquared) {
                $cityLng = (float) $city->longitude;
                $cityLat = (float) $city->latitude;
                $progress = (($cityLng - $fromLng) * $segmentDx + ($cityLat - $fromLat) * $segmentDy) / $segmentLengthSquared;

                return [
                    'city' => $city,
                    'progress' => $progress,
                ];
            })->sortBy('progress')->values();
        }

        foreach ($selectedCities as $entry) {
            $city = $entry['city'];
            foreach ($city->cityStops as $stop) {
                $journeyStops->push([
                    'id' => $stop->id,
                    'city_id' => $city->id,
                    'location_name' => $stop->location_name,
                    'address' => $stop->address,
                    'latitude' => $stop->latitude,
                    'longitude' => $stop->longitude,
                    'display_name' => $stop->location_name ?: $stop->address ?: $city->name,
                    'city' => ['id' => $city->id, 'name' => $city->name],
                    'sort_key' => $entry['progress'],
                ]);
            }
        }

        $journeyStops = $journeyStops->sortBy('sort_key')->values();

        return $this->success('Stops between fetched successfully.', $journeyStops);
    }
}
