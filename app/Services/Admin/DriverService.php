<?php

namespace App\Services\Admin;

use App\Enums\UserStatus;
use App\Models\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DriverService
{
    /**
     * Get paginated drivers with filters.
     */
    public function getDrivers(array $filters)
    {
        return Driver::query()

            ->with([
                'user',
            ])

            ->when(
                !empty($filters['status']),
                function ($query) use ($filters) {

                    $query->whereHas('user', function ($q) use ($filters) {

                        $q->where('status', $filters['status']);

                    });

                }
            )

            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {

                    $search = $filters['search'];

                    $query->whereHas('user', function ($q) use ($search) {

                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");

                    });

                }
            )

            ->latest()

            ->paginate(15)

            ->withQueryString();
    }

    /**
     * Get single driver.
     */
    public function getDriver(int $id): Driver
    {
        return Driver::with([
            'user',
            'approvedBy',
            'rejectedBy',
        ])->findOrFail($id);
    }

    /**
     * Approve driver.
     */
    public function approve(Driver $driver, int $adminId): void
    {
        $driver->user->update([
            'status' => UserStatus::APPROVED,
        ]);

        $driver->update([
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_issues' => null,
            'remarks' => 'Driver approved.',
        ]);
    }

    /**
     * Reject driver.
     */
    public function reject(
        Driver $driver,
        int $adminId,
        string $remarks,
        array $rejectionIssues = []
    ): void {

        $driver->user->update([
            'status' => UserStatus::REJECTED,
        ]);

        $driver->update([
            'rejected_by' => $adminId,
            'rejected_at' => now(),
            'remarks' => $remarks,
            'rejection_issues' => empty($rejectionIssues) ? null : array_values($rejectionIssues),
        ]);
    }

    public function getSummary(): array
    {
        return [

            'totalDrivers' => Driver::count(),

            'pendingDrivers' => Driver::whereHas('user', function ($query) {
                $query->where('status', 'pending');
            })->count(),

            'approvedDrivers' => Driver::whereHas('user', function ($query) {
                $query->where('status', 'approved');
            })->count(),

            'rejectedDrivers' => Driver::whereHas('user', function ($query) {
                $query->where('status', 'rejected');
            })->count(),

        ];
    }
}
