<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\Admin\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display all vehicles
     */
    public function index(Request $request)
    {
        $filters = [

            'status' => $request->status,

            'search' => $request->search,

        ];

        $vehicles = $this->vehicleService->getVehicles($filters);

        $summary = $this->vehicleService->getSummary();

        return view('admin.vehicles.index', compact(
            'vehicles',
            'summary',
            'filters'
        ));
    }

    /**
     * Show vehicle details
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle = $this->vehicleService->getVehicle($vehicle->id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Approve vehicle
     */
    public function approve(Vehicle $vehicle)
    {
        $this->vehicleService->approve(
            $vehicle,
            auth()->id()
        );

        return redirect()
            ->back()
            ->with('success', 'Vehicle approved successfully.');
    }

    /**
     * Reject vehicle
     */
    public function reject(
        Request $request,
        Vehicle $vehicle
    ) {

        $allowedIssues = [
            'vehicle_photo',
            'registration_book',
            'fitness_certificate',
            'insurance_document',
            'brand',
            'model',
            'manufacture_year',
            'color',
            'registration_number',
            'engine_number',
            'chassis_number',
            'total_seats',
            'available_seats',
        ];

        $request->validate([

            'remarks' => [
                'required',
                'string',
                'max:500',
            ],

            'rejection_issues' => ['nullable', 'array'],
            'rejection_issues.*' => ['string', Rule::in($allowedIssues)],

        ]);

        $this->vehicleService->reject(

            $vehicle,

            $request->remarks,

            auth()->id(),

            $request->input('rejection_issues', []),

        );

        return redirect()
            ->back()
            ->with('success', 'Vehicle rejected successfully.');
    }
}
