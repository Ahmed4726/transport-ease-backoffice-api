<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Http\Request;

class RouteStopAdminController extends Controller
{
    public function store(Request $request, Route $route)
    {
        $validated = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'stop_order' => ['required', 'integer'],
            'distance_from_start' => ['nullable', 'numeric'],
            'estimated_minutes' => ['nullable', 'integer'],
        ]);

        $route->stops()->create($validated);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Stop added successfully.');
    }

    public function edit(Route $route, RouteStop $stop)
    {
        $cities = City::orderBy('name')->get();

        return view('admin.routes.stops.edit', compact('route', 'stop', 'cities'));
    }

    public function update(Request $request, Route $route, RouteStop $stop)
    {
        $validated = $request->validate([
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'stop_order' => ['required', 'integer'],
            'distance_from_start' => ['nullable', 'numeric'],
            'estimated_minutes' => ['nullable', 'integer'],
        ]);

        $stop->update($validated);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Stop updated successfully.');
    }

    public function destroy(Route $route, RouteStop $stop)
    {
        $stop->delete();

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Stop removed successfully.');
    }
}
