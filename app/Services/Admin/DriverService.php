<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    public function createDriver(array $data, int $adminId): Driver
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::DRIVER,
            'status' => UserStatus::APPROVED,
        ]);

        $profilePhoto = $data['profile_photo']->store('drivers/profile', 'public');
        $cnicFront = $data['cnic_front']->store('drivers/cnic', 'public');
        $cnicBack = $data['cnic_back']->store('drivers/cnic', 'public');
        $licenseFront = $data['license_front']->store('drivers/license', 'public');
        $licenseBack = $data['license_back']->store('drivers/license', 'public');

        return Driver::create([
            'user_id' => $user->id,
            'cnic' => $data['cnic'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'license_expiry' => $data['license_expiry'] ?? null,
            'profile_photo' => $profilePhoto,
            'cnic_front' => $cnicFront,
            'cnic_back' => $cnicBack,
            'license_front' => $licenseFront,
            'license_back' => $licenseBack,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'is_available' => $data['is_available'] ?? false,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'remarks' => 'Created by admin and auto-approved.',
        ]);
    }

    public function updateDriver(Driver $driver, array $data): Driver
    {
        $driver->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => UserStatus::from($data['status']),
            'password' => isset($data['password']) ? Hash::make($data['password']) : $driver->user->password,
        ]);

        if (!empty($data['profile_photo'])) {
            Storage::disk('public')->delete($driver->profile_photo);
            $driver->profile_photo = $data['profile_photo']->store('drivers/profile', 'public');
        }

        if (!empty($data['cnic_front'])) {
            Storage::disk('public')->delete($driver->cnic_front);
            $driver->cnic_front = $data['cnic_front']->store('drivers/cnic', 'public');
        }

        if (!empty($data['cnic_back'])) {
            Storage::disk('public')->delete($driver->cnic_back);
            $driver->cnic_back = $data['cnic_back']->store('drivers/cnic', 'public');
        }

        if (!empty($data['license_front'])) {
            Storage::disk('public')->delete($driver->license_front);
            $driver->license_front = $data['license_front']->store('drivers/license', 'public');
        }

        if (!empty($data['license_back'])) {
            Storage::disk('public')->delete($driver->license_back);
            $driver->license_back = $data['license_back']->store('drivers/license', 'public');
        }

        $driver->cnic = $data['cnic'] ?? null;
        $driver->license_number = $data['license_number'] ?? null;
        $driver->license_expiry = $data['license_expiry'] ?? null;
        $driver->address = $data['address'] ?? null;
        $driver->city = $data['city'] ?? null;
        $driver->date_of_birth = $data['date_of_birth'] ?? null;
        $driver->emergency_contact_name = $data['emergency_contact_name'] ?? null;
        $driver->emergency_contact_phone = $data['emergency_contact_phone'] ?? null;
        $driver->blood_group = $data['blood_group'] ?? null;
        $driver->is_available = $data['is_available'] ?? false;

        $driver->save();

        return $driver->refresh();
    }

    public function deleteDriver(Driver $driver): void
    {
        $driver->user->delete();
        $driver->delete();
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
