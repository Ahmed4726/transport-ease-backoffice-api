<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityStop;
use App\Models\RouteStop;
use Illuminate\Http\Request;

class RouteStopController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index(Request $request)
    {
        $stops = RouteStop::with('city')->get();

        return $this->success('Stops fetched successfully.', $stops);
    }

    public function cityStops(City $city)
    {
        $stops = CityStop::with('city')
            ->where('city_id', $city->id)
            ->orderBy('id')
            ->get()
            ->map(function (CityStop $stop) {
                return array_merge($stop->toArray(), [
                    'display_name' => $stop->location_name
                        ?: $stop->address
                        ?: ($stop->city->name ?? 'Stop ' . $stop->id),
                ]);
            });

        return $this->success('City stops fetched successfully.', $stops);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => ['required', 'exists:routes,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'stop_order' => ['required', 'integer'],
            'distance_from_start' => ['nullable', 'numeric'],
            'estimated_minutes' => ['nullable', 'integer'],
        ]);

        $stop = RouteStop::create($validated);

        return $this->success('Stop created successfully.', $stop, 201);
    }

    public function update(Request $request, RouteStop $stop)
    {
        $validated = $request->validate([
            'stop_order' => ['nullable', 'integer'],
            'distance_from_start' => ['nullable', 'numeric'],
            'estimated_minutes' => ['nullable', 'integer'],
        ]);

        $stop->update($validated);

        return $this->success('Stop updated successfully.', $stop);
    }

    public function destroy(RouteStop $stop)
    {
        $stop->delete();

        return $this->success('Stop deleted successfully.');
    }
}
