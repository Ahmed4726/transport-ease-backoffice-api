<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\Admin\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function create()
    {
        $drivers = Driver::with('user')->get();
        $vehicleTypes = VehicleType::all();

        return view('admin.vehicles.create', compact('drivers', 'vehicleTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'vehicle_type_id' => ['required', 'integer', 'exists:vehicle_types,id'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'manufacture_year' => ['required', 'integer', 'min:1900', 'max:' . now()->year],
            'color' => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:100', 'unique:vehicles,registration_number'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'available_seats' => ['required', 'integer', 'min:1', 'lte:total_seats'],
            'vehicle_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'registration_book' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'fitness_certificate' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'insurance_document' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'vehicle_photos' => ['nullable', 'array'],
            'vehicle_photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $this->vehicleService->createVehicle($validated, Auth::id());

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle = $this->vehicleService->getVehicle($vehicle->id);
        $drivers = Driver::with('user')->get();
        $vehicleTypes = VehicleType::all();
        return view('admin.vehicles.edit', compact('vehicle', 'drivers', 'vehicleTypes'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'vehicle_type_id' => ['required', 'integer', 'exists:vehicle_types,id'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'manufacture_year' => ['required', 'integer', 'min:1900', 'max:' . now()->year],
            'color' => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:100', Rule::unique('vehicles', 'registration_number')->ignore($vehicle->id)],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'available_seats' => ['required', 'integer', 'min:1', 'lte:total_seats'],
            'vehicle_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'registration_book' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'fitness_certificate' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'insurance_document' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'vehicle_photos' => ['nullable', 'array'],
            'vehicle_photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $this->vehicleService->updateVehicle($vehicle, $validated);

        return redirect()
            ->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->vehicleService->deleteVehicle($vehicle);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
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
            Auth::id()
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

            Auth::id(),

            $request->input('rejection_issues', []),

        );

        return redirect()
            ->back()
            ->with('success', 'Vehicle rejected successfully.');
    }
}
