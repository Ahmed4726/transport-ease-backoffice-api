<?php

namespace App\Http\Requests\API\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_type_id' => [
                'required',
                'exists:vehicle_types,id',
            ],
            'brand' => [
                'required',
                'string',
                'max:100',
            ],
            'model' => [
                'required',
                'string',
                'max:100',
            ],
            'manufacture_year' => [
                'required',
                'digits:4',
                'integer',
                'min:1990',
                'max:' . date('Y'),
            ],
            'color' => [
                'required',
                'string',
                'max:50',
            ],
            'registration_number' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,registration_number,' . $this->vehicle?->id,
            ],
            'engine_number' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,engine_number,' . $this->vehicle?->id,
            ],
            'chassis_number' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,chassis_number,' . $this->vehicle?->id,
            ],
            'total_seats' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'available_seats' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'vehicle_photos' => [
                'nullable',
                'array',
                'min:5',
            ],
            'vehicle_photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
            'vehicle_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
            'registration_book' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'fitness_certificate' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'insurance_document' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.required' => 'Vehicle type is required.',
            'vehicle_type_id.exists' => 'Invalid vehicle type.',
            'registration_number.unique' => 'Registration number already exists.',
            'engine_number.unique' => 'Engine number already exists.',
            'chassis_number.unique' => 'Chassis number already exists.',
        ];
    }
}
