<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityStop;
use Illuminate\Http\Request;

class CityStopAdminController extends Controller
{
    public function index(City $city)
    {
        $stops = $city->cityStops()->get();

        return view('admin.cities.stops.index', compact('city', 'stops'));
    }

    public function create(City $city)
    {
        return view('admin.cities.stops.create', compact('city'));
    }

    public function store(Request $request, City $city)
    {
        $validated = $request->validate([
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['city_id'] = $city->id;

        CityStop::create($validated);

        return redirect()->route('admin.cities.stops.index', $city)
            ->with('success', 'Stop added to city successfully.');
    }

    public function edit(City $city, CityStop $stop)
    {
        return view('admin.cities.stops.edit', compact('city', 'stop'));
    }

    public function update(Request $request, City $city, CityStop $stop)
    {
        $validated = $request->validate([
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
        ]);

        $stop->update($validated);

        return redirect()->route('admin.cities.stops.index', $city)
            ->with('success', 'Stop updated successfully.');
    }

    public function destroy(City $city, CityStop $stop)
    {
        $stop->delete();

        return redirect()->route('admin.cities.stops.index', $city)
            ->with('success', 'Stop deleted successfully.');
    }
}
