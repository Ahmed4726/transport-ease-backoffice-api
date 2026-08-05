<?php

namespace App\Services\Admin;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    public function getVehicles(array $filters = []): LengthAwarePaginator
    {
        return Vehicle::query()

            ->with([
                'driver.user',
                'vehicleType',
                'approvedBy',
                'rejectedBy',
            ])

            ->when($filters['status'] ?? null, function ($query, $status) {

                $query->where('status', $status);

            })

            ->when($filters['search'] ?? null, function ($query, $search) {

                $query->where(function ($q) use ($search) {

                    $q->where('brand', 'LIKE', "%{$search}%")
                        ->orWhere('model', 'LIKE', "%{$search}%")
                        ->orWhere('registration_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('driver.user', function ($user) use ($search) {

                            $user->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");

                        });

                });

            })

            ->latest()

            ->paginate(15)

            ->withQueryString();
    }

    public function getVehicle(int $id): Vehicle
    {
        return Vehicle::with([

            'driver.user',

            'vehicleType',

            'approvedBy',

            'rejectedBy',

        ])->findOrFail($id);
    }

    public function getSummary(): array
    {
        return [

            'totalVehicles' => Vehicle::count(),

            'pendingVehicles' => Vehicle::where('status', VehicleStatus::PENDING)->count(),

            'approvedVehicles' => Vehicle::where('status', VehicleStatus::APPROVED)->count(),

            'rejectedVehicles' => Vehicle::where('status', VehicleStatus::REJECTED)->count(),

        ];
    }

    public function approve(Vehicle $vehicle, int $adminId): void
    {
        DB::transaction(function () use ($vehicle, $adminId) {

            $vehicle->update([

                'status' => VehicleStatus::APPROVED,

                'approved_by' => $adminId,

                'approved_at' => now(),

                'rejected_by' => null,

                'rejected_at' => null,

                'remarks' => 'Vehicle approved.',

            ]);

        });
    }

    public function reject(
        Vehicle $vehicle,
        string $remarks,
        int $adminId,
        array $rejectionIssues = []
    ): void {

        DB::transaction(function () use ($vehicle, $remarks, $adminId, $rejectionIssues) {

            $vehicle->update([

                'status' => VehicleStatus::REJECTED,

                'remarks' => $remarks,

                'rejected_by' => $adminId,

                'rejected_at' => now(),

                'approved_by' => null,

                'approved_at' => null,

                'rejection_issues' => empty($rejectionIssues) ? null : array_values($rejectionIssues),

            ]);

        });

    }
}
