<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Driver;
use App\Models\User;
use App\Services\Admin\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function index(Request $request)
    {
        $filters = [

            'status' => $request->get('status'),

            'search' => $request->get('search'),

        ];

        $drivers = $this->driverService->getDrivers($filters);

        $summary = $this->driverService->getSummary();

        return view('admin.drivers.index', [

            'drivers' => $drivers,

            'filters' => $filters,

            'summary' => $summary,

        ]);
    }

    public function show(Driver $driver)
    {
        $driver = $this->driverService->getDriver($driver->id);

        return view('admin.drivers.show', compact('driver'));
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cnic' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:100'],
            'license_expiry' => ['required', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_front' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_back' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_front' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_back' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $this->driverService->createDriver($validated, Auth::id());

        return redirect()
            ->route('admin.drivers.index')
            ->with('success', 'Driver created successfully.');
    }

    public function edit(Driver $driver)
    {
        $driver = $this->driverService->getDriver($driver->id);

        return view('admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($driver->user_id)],
            'phone' => ['required', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($driver->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in([UserStatus::APPROVED->value, UserStatus::PENDING->value, UserStatus::REJECTED->value])],
            'cnic' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:100'],
            'license_expiry' => ['required', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cnic_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'license_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $this->driverService->updateDriver($driver, $validated);

        return redirect()
            ->route('admin.drivers.show', $driver)
            ->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $this->driverService->deleteDriver($driver);

        return redirect()
            ->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }

    public function approve(Driver $driver)
    {
        $this->driverService->approve(
            $driver,
            Auth::id()
        );

        return redirect()
            ->back()
            ->with('success', 'Driver approved successfully.');
    }

    public function reject(Request $request, Driver $driver)
    {
        $allowedIssues = [
            'profile_photo',
            'cnic_front',
            'cnic_back',
            'license_front',
            'license_back',
            'address',
            'city',
            'date_of_birth',
            'emergency_contact_name',
            'emergency_contact_phone',
            'blood_group',
        ];

        $request->validate([
            'remarks' => ['required', 'string', 'max:500'],
            'rejection_issues' => ['nullable', 'array'],
            'rejection_issues.*' => ['string', Rule::in($allowedIssues)],
        ]);

        $this->driverService->reject(
            $driver,
            Auth::id(),
            $request->remarks,
            $request->input('rejection_issues', []),
        );

        return redirect()
            ->back()
            ->with('success', 'Driver rejected successfully.');
    }
}
