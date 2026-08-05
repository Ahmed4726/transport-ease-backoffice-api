<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteFare;
use App\Models\RouteStop;
use Illuminate\Http\Request;

class RouteFareAdminController extends Controller
{
    public function store(Request $request, Route $route)
    {
        $validated = $request->validate([
            'from_stop_id' => ['required', 'exists:route_stops,id'],
            'to_stop_id' => ['required', 'exists:route_stops,id'],
            'fare' => ['required', 'numeric'],
        ]);

        $route->fares()->create($validated);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Fare added successfully.');
    }

    public function edit(Route $route, RouteFare $fare)
    {
        $stops = $route->stops()->with('city')->orderBy('stop_order')->get();

        return view('admin.routes.fares.edit', compact('route', 'fare', 'stops'));
    }

    public function update(Request $request, Route $route, RouteFare $fare)
    {
        $validated = $request->validate([
            'from_stop_id' => ['required', 'exists:route_stops,id'],
            'to_stop_id' => ['required', 'exists:route_stops,id'],
            'fare' => ['required', 'numeric'],
        ]);

        $fare->update($validated);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Fare updated successfully.');
    }

    public function destroy(Route $route, RouteFare $fare)
    {
        $fare->delete();

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Fare removed successfully.');
    }
}
