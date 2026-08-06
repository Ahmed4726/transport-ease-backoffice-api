<?php

namespace App\Services\Admin;

use App\Models\Driver;
use App\Models\DriverTrip;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(): array
    {
        $driverUsersQuery = User::query()
            ->where('role', 'driver')
            ->whereHas('driver');

        $passengerUsersQuery = User::query()
            ->where('role', 'passenger');

        $vehicleStatusCounts = Vehicle::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $tripStatusCounts = DriverTrip::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'totalDrivers' => $driverUsersQuery->count(),
            'driverStatusCounts' => [
                'approved' => (clone $driverUsersQuery)->where('status', 'approved')->count(),
                'pending' => (clone $driverUsersQuery)->where('status', 'pending')->count(),
                'rejected' => (clone $driverUsersQuery)->where('status', 'rejected')->count(),
            ],
            'totalPassengers' => $passengerUsersQuery->count(),
            'passengerStatusCounts' => [
                'approved' => (clone $passengerUsersQuery)->where('status', 'approved')->count(),
                'pending' => (clone $passengerUsersQuery)->where('status', 'pending')->count(),
                'rejected' => (clone $passengerUsersQuery)->where('status', 'rejected')->count(),
            ],
            'totalVehicles' => Vehicle::count(),
            'vehicleStatusCounts' => [
                'approved' => $vehicleStatusCounts['approved'] ?? 0,
                'pending' => $vehicleStatusCounts['pending'] ?? 0,
                'rejected' => $vehicleStatusCounts['rejected'] ?? 0,
            ],
            'totalTrips' => DriverTrip::count(),
            'tripStatusCounts' => [
                'scheduled' => $tripStatusCounts['scheduled'] ?? 0,
                'started' => $tripStatusCounts['started'] ?? 0,
                'completed' => $tripStatusCounts['completed'] ?? 0,
                'cancelled' => $tripStatusCounts['cancelled'] ?? 0,
            ],
            'recentDrivers' => (clone $driverUsersQuery)
                ->with('driver')
                ->latest()
                ->take(10)
                ->get(),
            'recentPassengers' => (clone $passengerUsersQuery)
                ->latest()
                ->take(10)
                ->get(),
            'recentVehicles' => Vehicle::with('driver.user')
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
}
