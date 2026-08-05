<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'driver_id' => $this->driver_id,

            'vehicle_type_id' => $this->vehicle_type_id,

            'vehicle_type' => $this->vehicleType?->name,

            'brand' => $this->brand,

            'model' => $this->model,

            'manufacture_year' => $this->manufacture_year,

            'color' => $this->color,

            'registration_number' => $this->registration_number,

            'engine_number' => $this->engine_number,

            'chassis_number' => $this->chassis_number,

            'total_seats' => $this->total_seats,

            'available_seats' => $this->available_seats,

            'vehicle_photo' => $this->vehicle_photo
                ? asset('storage/'.$this->vehicle_photo)
                : null,

            'vehicle_photos' => $this->vehicle_photos
                ? array_map(fn ($photo) => asset('storage/'.$photo), $this->vehicle_photos)
                : [],

            'registration_book' => $this->registration_book
                ? asset('storage/'.$this->registration_book)
                : null,

            'fitness_certificate' => $this->fitness_certificate
                ? asset('storage/'.$this->fitness_certificate)
                : null,

            'insurance_document' => $this->insurance_document
                ? asset('storage/'.$this->insurance_document)
                : null,

            'status' => $this->status->value,

            'remarks' => $this->remarks,

            'rejection_issues' => $this->rejection_issues,

            'rejection_histories' => $this->whenLoaded('rejectionHistories', function () {
                return $this->rejectionHistories->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'admin_name' => optional($history->admin)->name,
                        'remarks' => $history->remarks,
                        'rejection_issues' => $history->rejection_issues,
                        'created_at' => $history->created_at,
                    ];
                });
            }),

            'approved_at' => $this->approved_at,

            'created_at' => $this->created_at,

        ];
    }
}
