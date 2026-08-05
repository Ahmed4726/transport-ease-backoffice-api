<?php

namespace App\Services\Vehicle;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleRejectionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleService
{
    public function getCurrentVehicle(User $user): ?Vehicle
    {
        if (!$user->driver) {
            return null;
        }

        return Vehicle::with(['vehicleType', 'rejectionHistories'])
            ->where('driver_id', $user->driver->id)
            ->latest()
            ->first();
    }

    public function register(array $data, User $user): Vehicle
    {
        $driver = $user->driver;

        if (!$driver) {
            throw new \RuntimeException('Driver profile not found.');
        }

        $latestVehicle = $driver->vehicles()->latest()->first();
        if ($latestVehicle && in_array($latestVehicle->status, [VehicleStatus::PENDING, VehicleStatus::REJECTED], true)) {
            throw new \RuntimeException('You already have an active vehicle request. Please update the existing vehicle request instead of creating a new one.');
        }

        return DB::transaction(function () use ($data, $driver) {
            $vehiclePhotos = [];
            if (!empty($data['vehicle_photos'])) {
                foreach ($data['vehicle_photos'] as $photo) {
                    $vehiclePhotos[] = $photo->store('vehicles/photos', 'public');
                }
            }

            $vehiclePhoto = count($vehiclePhotos) ? $vehiclePhotos[0] : null;
            $registrationBook = $data['registration_book']->store('vehicles/registration_books', 'public');
            $fitnessCertificate = isset($data['fitness_certificate'])
                ? $data['fitness_certificate']->store('vehicles/fitness_certificates', 'public')
                : null;
            $insuranceDocument = isset($data['insurance_document'])
                ? $data['insurance_document']->store('vehicles/insurance_documents', 'public')
                : null;

            $vehicle = $driver->vehicles()->create([
                'vehicle_type_id' => $data['vehicle_type_id'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'manufacture_year' => $data['manufacture_year'],
                'color' => $data['color'],
                'registration_number' => $data['registration_number'],
                'engine_number' => $data['engine_number'],
                'chassis_number' => $data['chassis_number'],
                'total_seats' => $data['total_seats'],
                'available_seats' => $data['available_seats'],
                'vehicle_photo' => $vehiclePhoto,
                'vehicle_photos' => $vehiclePhotos,
                'registration_book' => $registrationBook,
                'fitness_certificate' => $fitnessCertificate,
                'insurance_document' => $insuranceDocument,
                'status' => VehicleStatus::PENDING,
                'remarks' => 'Vehicle registration submitted for review.',
            ]);

            return $vehicle;
        });
    }

    public function updateVehicle(Vehicle $vehicle, array $data, User $user): Vehicle
    {
        if ($vehicle->driver_id !== $user->driver->id) {
            throw new \RuntimeException('Unauthorized to update this vehicle.');
        }

        if ($vehicle->status === VehicleStatus::APPROVED) {
            throw new \RuntimeException('Approved vehicles cannot be updated through this endpoint.');
        }

        return DB::transaction(function () use ($vehicle, $data) {
            if (isset($data['vehicle_photos'])) {
                if (!empty($vehicle->vehicle_photos)) {
                    foreach ($vehicle->vehicle_photos as $existingPhoto) {
                        Storage::disk('public')->delete($existingPhoto);
                    }
                }

                $vehiclePhotos = [];
                foreach ($data['vehicle_photos'] as $photo) {
                    $vehiclePhotos[] = $photo->store('vehicles/photos', 'public');
                }
                $vehiclePhoto = count($vehiclePhotos) ? $vehiclePhotos[0] : $vehicle->vehicle_photo;
            } else {
                $vehiclePhotos = $vehicle->vehicle_photos;
                $vehiclePhoto = $vehicle->vehicle_photo;
            }

            if (isset($data['registration_book'])) {
                Storage::disk('public')->delete($vehicle->registration_book);
                $registrationBook = $data['registration_book']->store('vehicles/registration_books', 'public');
            } else {
                $registrationBook = $vehicle->registration_book;
            }

            if (isset($data['fitness_certificate'])) {
                Storage::disk('public')->delete($vehicle->fitness_certificate);
                $fitnessCertificate = $data['fitness_certificate']->store('vehicles/fitness_certificates', 'public');
            } else {
                $fitnessCertificate = $vehicle->fitness_certificate;
            }

            if (isset($data['insurance_document'])) {
                Storage::disk('public')->delete($vehicle->insurance_document);
                $insuranceDocument = $data['insurance_document']->store('vehicles/insurance_documents', 'public');
            } else {
                $insuranceDocument = $vehicle->insurance_document;
            }

            $vehicle->update([
                'vehicle_type_id' => $data['vehicle_type_id'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'manufacture_year' => $data['manufacture_year'],
                'color' => $data['color'],
                'registration_number' => $data['registration_number'],
                'engine_number' => $data['engine_number'],
                'chassis_number' => $data['chassis_number'],
                'total_seats' => $data['total_seats'],
                'available_seats' => $data['available_seats'],
                'vehicle_photo' => $vehiclePhoto,
                'vehicle_photos' => $vehiclePhotos,
                'registration_book' => $registrationBook,
                'fitness_certificate' => $fitnessCertificate,
                'insurance_document' => $insuranceDocument,
                'status' => VehicleStatus::PENDING,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_issues' => null,
                'remarks' => 'Vehicle updated and resubmitted for review.',
            ]);

            return $vehicle;
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

            VehicleRejectionHistory::create([
                'vehicle_id' => $vehicle->id,
                'admin_id' => $adminId,
                'remarks' => $remarks,
                'rejection_issues' => empty($rejectionIssues) ? null : array_values($rejectionIssues),
            ]);
        });
    }
}
