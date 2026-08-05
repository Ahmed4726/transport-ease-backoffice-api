<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRegisterRequest;
use App\Http\Requests\PassengerRegisterRequest;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Passenger Registration
     */
    public function registerPassenger(PassengerRegisterRequest $request)
    {
        $response = $this->authService
            ->registerPassenger($request->validated());

        if (!$response['success']) {
            return $this->error($response['message']);
        }

        return $this->success(
            $response['message'],
            $response['data'],
            201
        );
    }

    /**
     * Driver Registration
     */
    public function registerDriver(DriverRegisterRequest $request)
    {
        $response = $this->authService
            ->registerDriver($request->validated());

        if (!$response['success']) {
            return $this->error($response['message']);
        }

        return $this->success(
            $response['message'],
            $response['data'],
            201
        );
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {

            return $this->error(
                'Invalid email or password.',
                [],
                401
            );
        }

        // Delete previous tokens
        $user->tokens()->delete();

        $token = $this->authService->login($user);

        return $this->success(
            'Login successful.',
            [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user->load('driver')),
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logged out successfully.');
    }

    private function userIsDriver(User $user): bool
    {
        $role = $user->role;

        return $role instanceof UserRole
            ? $role === UserRole::DRIVER
            : (string) $role === UserRole::DRIVER->value;
    }

    private function userIsRejected(User $user): bool
    {
        $status = $user->status;
        $statusValue = $status instanceof UserStatus ? $status->value : (string) $status;

        return $statusValue === UserStatus::REJECTED->value;
    }

    public function me(Request $request)
    {
        $user = $request->user()->load([
            'driver',
            'driver.vehicle.vehicleType',
        ]);

        return $this->success(
            'Profile fetched successfully.',
            new UserResource($user),
        );
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('driver');

        return $this->success(
            'Profile fetched successfully.',
            new UserResource($user)
        );
    }

    public function updateAvailability(Request $request)
    {
        $user = $request->user();

        if (!$this->userIsDriver($user)) {
            return $this->error('Unauthorized.', [], 403);
        }

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        $driver->update([
            'is_available' => $validated['is_available'],
        ]);

        return $this->success(
            'Availability updated successfully.',
            new UserResource($user->fresh()->load('driver'))
        );
    }

    public function updateDriverProfile(Request $request)
    {
        $user = $request->user();

        if (!$this->userIsDriver($user)) {
            return $this->error('Unauthorized.', [], 403);
        }

        if (!$this->userIsRejected($user)) {
            return $this->error(
                'Only rejected drivers can update their application.',
                [],
                403
            );
        }

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_issue_date' => ['nullable', 'date'],
            'cnic_expiry_date' => ['nullable', 'date'],
            'license_issue_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('profile_photo')) {
            Storage::disk('public')->delete($driver->profile_photo);
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('drivers/profile', 'public');
        }

        if ($request->hasFile('cnic_front')) {
            Storage::disk('public')->delete($driver->cnic_front);
            $validated['cnic_front'] = $request->file('cnic_front')
                ->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('cnic_back')) {
            Storage::disk('public')->delete($driver->cnic_back);
            $validated['cnic_back'] = $request->file('cnic_back')
                ->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('license_front')) {
            Storage::disk('public')->delete($driver->license_front);
            $validated['license_front'] = $request->file('license_front')
                ->store('drivers/license', 'public');
        }

        if ($request->hasFile('license_back')) {
            Storage::disk('public')->delete($driver->license_back);
            $validated['license_back'] = $request->file('license_back')
                ->store('drivers/license', 'public');
        }

        $driver->update(array_filter([
            'address' => $validated['address'] ?? $driver->address,
            'city' => $validated['city'] ?? $driver->city,
            'date_of_birth' => $validated['date_of_birth'] ?? $driver->date_of_birth,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? $driver->emergency_contact_name,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $driver->emergency_contact_phone,
            'blood_group' => $validated['blood_group'] ?? $driver->blood_group,
            'profile_photo' => $validated['profile_photo'] ?? $driver->profile_photo,
            'cnic_front' => $validated['cnic_front'] ?? $driver->cnic_front,
            'cnic_back' => $validated['cnic_back'] ?? $driver->cnic_back,
            'license_front' => $validated['license_front'] ?? $driver->license_front,
            'license_back' => $validated['license_back'] ?? $driver->license_back,
            'remarks' => 'Application updated and resubmitted for review.',
        ]));

        $user->update([
            'status' => UserStatus::PENDING->value,
        ]);

        return $this->success(
            'Application updated and resubmitted successfully.',
            new UserResource($user->fresh()->load('driver'))
        );
    }


    public function updatePersonalProfile(Request $request)
    {
        $user = $request->user();

        if (!$this->userIsDriver($user)) {
            return $this->error('Unauthorized.', [], 403);
        }

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_issue_date' => ['nullable', 'date'],
            'cnic_expiry_date' => ['nullable', 'date'],
            'license_issue_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('profile_photo')) {
            Storage::disk('public')->delete($driver->profile_photo);
            $validated['profile_photo'] = $request->file('profile_photo')->store('drivers/profile', 'public');
        }

        if ($request->hasFile('cnic_front')) {
            Storage::disk('public')->delete($driver->cnic_front);
            $validated['cnic_front'] = $request->file('cnic_front')->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('cnic_back')) {
            Storage::disk('public')->delete($driver->cnic_back);
            $validated['cnic_back'] = $request->file('cnic_back')->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('license_front')) {
            Storage::disk('public')->delete($driver->license_front);
            $validated['license_front'] = $request->file('license_front')->store('drivers/license', 'public');
        }

        if ($request->hasFile('license_back')) {
            Storage::disk('public')->delete($driver->license_back);
            $validated['license_back'] = $request->file('license_back')->store('drivers/license', 'public');
        }

        $user->update(array_filter([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? $user->phone,
        ], fn ($value) => $value !== null));

        $driver->update(array_filter([
            'address' => $validated['address'] ?? $driver->address,
            'city' => $validated['city'] ?? $driver->city,
            'date_of_birth' => $validated['date_of_birth'] ?? $driver->date_of_birth,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? $driver->emergency_contact_name,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $driver->emergency_contact_phone,
            'blood_group' => $validated['blood_group'] ?? $driver->blood_group,
            'profile_photo' => $validated['profile_photo'] ?? $driver->profile_photo,
            'cnic_front' => $validated['cnic_front'] ?? $driver->cnic_front,
            'cnic_back' => $validated['cnic_back'] ?? $driver->cnic_back,
            'license_front' => $validated['license_front'] ?? $driver->license_front,
            'license_back' => $validated['license_back'] ?? $driver->license_back,
            'cnic_issue_date' => $validated['cnic_issue_date'] ?? $driver->cnic_issue_date ?? null,
            'cnic_expiry_date' => $validated['cnic_expiry_date'] ?? $driver->cnic_expiry_date ?? null,
            'license_issue_date' => $validated['license_issue_date'] ?? $driver->license_issue_date ?? null,
            'license_expiry_date' => $validated['license_expiry_date'] ?? $driver->license_expiry_date ?? $driver->license_expiry,
        ], fn ($value) => $value !== null));

        return $this->success(
            'Personal profile updated successfully.',
            new UserResource($user->fresh()->load('driver'))
        );
    }

    public function resubmitApplication(Request $request)
    {
        $user = $request->user();

        if (!$this->userIsDriver($user)) {
            return $this->error('Unauthorized.', [], 403);
        }

        if (!$this->userIsRejected($user)) {
            return $this->error(
                'Only rejected drivers can resubmit their application.',
                [],
                403
            );
        }

        $driver = $user->driver;

        if (!$driver) {
            return $this->error('Driver profile not found.', [], 404);
        }

        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_front' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_back' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_issue_date' => ['nullable', 'date'],
            'cnic_expiry_date' => ['nullable', 'date'],
            'license_issue_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('profile_photo')) {
            Storage::disk('public')->delete($driver->profile_photo);
            $validated['profile_photo'] = $request->file('profile_photo')->store('drivers/profile', 'public');
        }

        if ($request->hasFile('cnic_front')) {
            Storage::disk('public')->delete($driver->cnic_front);
            $validated['cnic_front'] = $request->file('cnic_front')->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('cnic_back')) {
            Storage::disk('public')->delete($driver->cnic_back);
            $validated['cnic_back'] = $request->file('cnic_back')->store('drivers/cnic', 'public');
        }

        if ($request->hasFile('license_front')) {
            Storage::disk('public')->delete($driver->license_front);
            $validated['license_front'] = $request->file('license_front')->store('drivers/license', 'public');
        }

        if ($request->hasFile('license_back')) {
            Storage::disk('public')->delete($driver->license_back);
            $validated['license_back'] = $request->file('license_back')->store('drivers/license', 'public');
        }

        $driver->update(array_filter([
            'address' => $validated['address'] ?? $driver->address,
            'city' => $validated['city'] ?? $driver->city,
            'date_of_birth' => $validated['date_of_birth'] ?? $driver->date_of_birth,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? $driver->emergency_contact_name,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $driver->emergency_contact_phone,
            'blood_group' => $validated['blood_group'] ?? $driver->blood_group,
            'profile_photo' => $validated['profile_photo'] ?? $driver->profile_photo,
            'cnic_front' => $validated['cnic_front'] ?? $driver->cnic_front,
            'cnic_back' => $validated['cnic_back'] ?? $driver->cnic_back,
            'license_front' => $validated['license_front'] ?? $driver->license_front,
            'license_back' => $validated['license_back'] ?? $driver->license_back,
            'remarks' => 'Application updated and resubmitted for review.',
            'cnic_issue_date' => $validated['cnic_issue_date'] ?? $driver->cnic_issue_date ?? null,
            'cnic_expiry_date' => $validated['cnic_expiry_date'] ?? $driver->cnic_expiry_date ?? null,
            'license_issue_date' => $validated['license_issue_date'] ?? $driver->license_issue_date ?? null,
            'license_expiry_date' => $validated['license_expiry_date'] ?? $driver->license_expiry_date ?? $driver->license_expiry,
        ]));

        $user->update([
            'status' => UserStatus::PENDING->value,
            'rejected_at' => null,
        ]);

        $driver->update([
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_issues' => null,
        ]);

        return $this->success(
            'Application resubmitted successfully and moved to review.',
            new UserResource($user->fresh()->load('driver'))
        );
    }
}
