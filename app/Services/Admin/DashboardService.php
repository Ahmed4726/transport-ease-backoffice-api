<?php

namespace App\Services\Admin;

use App\Models\Driver;
use App\Models\User;

class DashboardService
{
    public function getDashboardData(): array
    {
        $driverUsersQuery = User::query()
            ->where('role', 'driver')
            ->whereHas('driver');

        return [
            'totalDrivers' => $driverUsersQuery->count(),

            'approvedDrivers' => (clone $driverUsersQuery)
                ->where('status', 'approved')
                ->count(),

            'pendingDrivers' => (clone $driverUsersQuery)
                ->where('status', 'pending')
                ->count(),

            'rejectedDrivers' => (clone $driverUsersQuery)
                ->where('status', 'rejected')
                ->count(),

            'totalPassengers' => User::where('role', 'passenger')->count(),

            'recentDrivers' => (clone $driverUsersQuery)
                ->with('driver')
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
}
