<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PassengerRegisterRequest extends FormRequest
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
        ];
    }
}
