<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverTrip;
use App\Models\DriverTripLocation;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Http\Request;

class DriverTripLocationController extends Controller
{
    use \App\Traits\ApiResponse;

    public function store(Request $request, DriverTrip $driverTrip)
    {
        $user = $request->user();
        if (!$this->ownsApprovedTrip($user, $driverTrip)) {
            return $this->error('Unauthorized.', [], 403);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        DriverTripLocation::updateOrCreate([
            'driver_trip_id' => $driverTrip->id,
        ], [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => now(),
        ]);

        return $this->success('Location updated successfully.');
    }

    public function latest(Request $request, DriverTrip $driverTrip)
    {
        if (!$this->ownsApprovedTrip($request->user(), $driverTrip)) {
            return $this->error('Unauthorized.', [], 403);
        }

        $location = $driverTrip->locations()->latest('recorded_at')->first();

        return $this->success('Location fetched successfully.', $location);
    }

    private function ownsApprovedTrip($user, DriverTrip $driverTrip): bool
    {
        return $user
            && $user->role === UserRole::DRIVER
            && $user->status === UserStatus::APPROVED
            && $user->driver
            && $driverTrip->driver_id === $user->driver->id;
    }
}
