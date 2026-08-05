<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteAdminController extends Controller
{
    public function index(Request $request)
    {
        $routes = Route::with(['startCity', 'endCity'])->get();

        return view('admin.routes.index', compact('routes'));
    }

    public function show(Route $route)
    {
        $route->load(['startCity', 'endCity', 'stops.city', 'fares.fromStop.city', 'fares.toStop.city']);

        return view('admin.routes.show', compact('route'));
    }
}
