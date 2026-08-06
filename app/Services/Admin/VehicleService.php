<?php

namespace App\Services\Admin;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function createVehicle(array $data, int $adminId): Vehicle
    {
        $vehiclePhoto = $data['vehicle_photo']->store('vehicles/main', 'public');
        $registrationBook = $data['registration_book']->store('vehicles/documents', 'public');
        $fitnessCertificate = isset($data['fitness_certificate']) ? $data['fitness_certificate']->store('vehicles/documents', 'public') : null;
        $insuranceDocument = isset($data['insurance_document']) ? $data['insurance_document']->store('vehicles/documents', 'public') : null;
        $vehiclePhotos = [];

        if (!empty($data['vehicle_photos'])) {
            foreach ($data['vehicle_photos'] as $photo) {
                if ($photo) {
                    $vehiclePhotos[] = $photo->store('vehicles/photos', 'public');
                }
            }
        }

        return Vehicle::create([
            'driver_id' => $data['driver_id'],
            'vehicle_type_id' => $data['vehicle_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'manufacture_year' => $data['manufacture_year'],
            'color' => $data['color'],
            'registration_number' => $data['registration_number'],
            'engine_number' => $data['engine_number'] ?? null,
            'chassis_number' => $data['chassis_number'] ?? null,
            'total_seats' => $data['total_seats'],
            'available_seats' => $data['available_seats'],
            'vehicle_photo' => $vehiclePhoto,
            'registration_book' => $registrationBook,
            'fitness_certificate' => $fitnessCertificate,
            'insurance_document' => $insuranceDocument,
            'vehicle_photos' => empty($vehiclePhotos) ? null : $vehiclePhotos,
            'status' => VehicleStatus::APPROVED,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'remarks' => 'Created and approved by admin.',
        ]);
    }

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        if (!empty($data['vehicle_photo'])) {
            Storage::disk('public')->delete($vehicle->vehicle_photo);
            $vehicle->vehicle_photo = $data['vehicle_photo']->store('vehicles/main', 'public');
        }

        if (!empty($data['registration_book'])) {
            Storage::disk('public')->delete($vehicle->registration_book);
            $vehicle->registration_book = $data['registration_book']->store('vehicles/documents', 'public');
        }

        if (!empty($data['fitness_certificate'])) {
            Storage::disk('public')->delete($vehicle->fitness_certificate);
            $vehicle->fitness_certificate = $data['fitness_certificate']->store('vehicles/documents', 'public');
        }

        if (!empty($data['insurance_document'])) {
            Storage::disk('public')->delete($vehicle->insurance_document);
            $vehicle->insurance_document = $data['insurance_document']->store('vehicles/documents', 'public');
        }

        if (!empty($data['vehicle_photos'])) {
            $existingPhotos = $vehicle->vehicle_photos ?? [];
            foreach ($existingPhotos as $photo) {
                Storage::disk('public')->delete($photo);
            }
            $vehiclePhotos = [];
            foreach ($data['vehicle_photos'] as $photo) {
                if ($photo) {
                    $vehiclePhotos[] = $photo->store('vehicles/photos', 'public');
                }
            }
            $vehicle->vehicle_photos = $vehiclePhotos;
        }

        $vehicle->fill([
            'driver_id' => $data['driver_id'],
            'vehicle_type_id' => $data['vehicle_type_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'manufacture_year' => $data['manufacture_year'],
            'color' => $data['color'],
            'registration_number' => $data['registration_number'],
            'engine_number' => $data['engine_number'] ?? null,
            'chassis_number' => $data['chassis_number'] ?? null,
            'total_seats' => $data['total_seats'],
            'available_seats' => $data['available_seats'],
        ]);

        $vehicle->save();

        return $vehicle->refresh();
    }

    public function deleteVehicle(Vehicle $vehicle): void
    {
        $vehicle->delete();
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
