<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Services\Admin\DriverService;
use Illuminate\Http\Request;
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

    public function approve(Driver $driver)
    {
        $this->driverService->approve(
            $driver,
            auth()->id()
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
            auth()->id(),
            $request->remarks,
            $request->input('rejection_issues', []),
        );

        return redirect()
            ->back()
            ->with('success', 'Driver rejected successfully.');
    }
}
