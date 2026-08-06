<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverTrip;
use App\Models\DriverTripLocation;
use Illuminate\Http\Request;

class DriverTripLocationController extends Controller
{
    use \App\Traits\ApiResponse;

    public function store(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        if (!$user?->driver || $driverTrip->driver_id !== $user->driver->id) {
            return $this->error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        DriverTripLocation::create([
            'driver_trip_id' => $driverTrip->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return $this->success('Location updated successfully.');
    }

    public function latest(Request $request, DriverTrip $driverTrip)
    {
        $location = $driverTrip->locations()->latest('recorded_at')->first();

        return $this->success('Location fetched successfully.', $location);
    }
}
