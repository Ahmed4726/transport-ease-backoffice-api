<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'cnic' => $this->cnic,

            'license_number' => $this->license_number,

            'license_expiry' => $this->license_expiry,

            'address' => $this->address,

            'city' => $this->city,

            'date_of_birth' => optional($this->date_of_birth)->toDateString(),

            'emergency_contact_name' => $this->emergency_contact_name,

            'emergency_contact_phone' => $this->emergency_contact_phone,

            'blood_group' => $this->blood_group,

            'is_available' => $this->is_available,

            'remarks' => $this->remarks,

            'rejection_issues' => $this->rejection_issues,

            'profile_photo' => $this->profile_photo,

            'cnic_front' => $this->cnic_front,

            'cnic_back' => $this->cnic_back,

            'license_front' => $this->license_front,

            'license_back' => $this->license_back,

            'vehicle' => $this->whenLoaded(
                'vehicle',
                fn () => new VehicleResource($this->vehicle),
            ),

        ];
    }
}
