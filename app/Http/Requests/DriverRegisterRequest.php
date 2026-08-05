<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email', 'unique:users,email'],

            'phone' => ['required', 'string', 'unique:users,phone'],

            'password' => ['required', 'confirmed', 'min:8'],

            'cnic' => [
                'required',
                'string',
                'unique:drivers,cnic',
            ],

            'license_number' => [
                'required',
                'string',
                'unique:drivers,license_number',
            ],

            'license_expiry' => [
                'required',
                'date',
                'after:today',
            ],

            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'cnic_front' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'cnic_back' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'license_front' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'license_back' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'route_id.exists' => 'Selected route does not exist.',
            'license_expiry.after' => 'License expiry must be a future date.',
            'route_permit.max' => 'Permit must not exceed 5 MB.',
        ];
    }
}
