<?php

namespace App\Services;

use App\Mail\RegistrationReceived;
use App\Models\User;
use App\Models\Driver;
use App\Models\Passenger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    public function registerPassenger(array $data)
    {
        DB::beginTransaction();

        try {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'passenger',
                'status' => 'approved',
            ]);

            Passenger::create([
                'user_id' => $user->id,
            ]);

            $token = $user->createToken('transport-system')->plainTextToken;

            DB::commit();

            try {
                Mail::to($user->email)
                    ->send(new RegistrationReceived($user));
            } catch (\Throwable $e) {
                report($e);
            }

            return [
                'success' => true,
                'message' => 'Passenger registered successfully.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function registerDriver(array $data)
    {
        DB::beginTransaction();

        try {

            $profilePhoto = $data['profile_photo']->store(
                'drivers/profile',
                'public'
            );

            $cnicFront = $data['cnic_front']->store(
                'drivers/cnic',
                'public'
            );

            $cnicBack = $data['cnic_back']->store(
                'drivers/cnic',
                'public'
            );

            $licenseFront = $data['license_front']->store(
                'drivers/license',
                'public'
            );

            $licenseBack = $data['license_back']->store(
                'drivers/license',
                'public'
            );

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'driver',
                'status' => 'pending',
            ]);

            Driver::create([
                'user_id' => $user->id,
                'cnic' => $data['cnic'],
                'license_number' => $data['license_number'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'license_expiry' => $data['license_expiry'],
                'profile_photo' => $profilePhoto,
                'cnic_front' => $cnicFront,
                'cnic_back' => $cnicBack,
                'license_front' => $licenseFront,
                'license_back' => $licenseBack,
            ]);

            DB::commit();

            try {
                Mail::to($user->email)
                    ->send(new RegistrationReceived($user));
            } catch (\Throwable $e) {
                report($e);
            }

            return [
                'success' => true,
                'message' => 'Registration submitted successfully.',
                'data' => [
                    'user' => $user,
                ]
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            if (isset($permit)) {
                Storage::disk('public')->delete($permit);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function login(User $user)
    {
        $user->tokens()->delete();

        return $user->createToken(
            'transport-system'
        )->plainTextToken;
    }
}
