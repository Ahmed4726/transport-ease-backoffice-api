<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityStop;
use App\Models\Driver;
use App\Models\DriverTrip;
use App\Models\Route;
use App\Models\Vehicle;
use App\Services\Admin\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $trips = $this->tripService->getTrips($filters);

        return view('admin.trips.index', compact('trips', 'filters'));
    }

    public function create()
    {
        $drivers = Driver::with('user')->whereHas('vehicles', function ($query) {
            $query->where('status', VehicleStatus::APPROVED);
        })->get();
        $vehicles = Vehicle::with('driver.user')->where('status', VehicleStatus::APPROVED)->get();
        $routes = Route::all();
        $cities = City::with(['cityStops' => function ($query) {
            $query->orderBy('location_name')->orderBy('address');
        }])->whereHas('cityStops')->orderBy('name')->get();
        $driverVehicleMap = $this->buildDriverVehicleMap($drivers);
        $citiesData = $this->buildCitiesData($cities);

        return view('admin.trips.create', compact('drivers', 'vehicles', 'routes', 'cities', 'citiesData', 'driverVehicleMap'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'selected_stop_ids' => $this->normalizeSelectedStopIds($request->input('selected_stop_ids')),
        ]);

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'trip_date' => ['nullable', 'date'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'available_seats' => ['required', 'integer', 'min:1', 'lte:total_seats'],
            'from_stop_id' => ['required', 'integer', 'exists:city_stops,id'],
            'to_stop_id' => ['required', 'integer', 'exists:city_stops,id', 'different:from_stop_id'],
            'selected_stop_ids' => ['nullable', 'array'],
            'selected_stop_ids.*' => ['integer', 'exists:city_stops,id'],
            'is_instant' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['scheduled', 'started', 'completed', 'cancelled'])],
        ]);

        $this->validateTripVehicleSelection($validated);
        $this->tripService->createTrip($validated, Auth::id());

        return redirect()->route('admin.trips.index')->with('success', 'Trip created successfully.');
    }

    public function show(DriverTrip $trip)
    {
        $trip = $this->tripService->getTrip($trip->id);

        return view('admin.trips.show', compact('trip'));
    }

    public function edit(DriverTrip $trip)
    {
        $drivers = Driver::with('user')->whereHas('vehicles', function ($query) {
            $query->where('status', VehicleStatus::APPROVED);
        })->get();
        $vehicles = Vehicle::with('driver.user')->where('status', VehicleStatus::APPROVED)->get();
        $routes = Route::all();
        $cities = City::with(['cityStops' => function ($query) {
            $query->orderBy('location_name')->orderBy('address');
        }])->whereHas('cityStops')->orderBy('name')->get();

        $trip->load(['stops' => function ($query) {
            $query->orderBy('stop_order');
        }]);

        $driverVehicleMap = $this->buildDriverVehicleMap($drivers);
        $citiesData = $this->buildCitiesData($cities);
        $selectedVehicle = $this->getApprovedVehicleForDriver($trip->driver);
        $selectedVehicleId = $selectedVehicle?->id ?? $trip->vehicle_id;
        $selectedVehicleCapacity = max(1, (int) ($selectedVehicle?->available_seats ?? $trip->vehicle?->available_seats ?? 1));

        $tripStopIds = $trip->stops->pluck('route_stop_id')->values()->all();
        $fromStopId = $tripStopIds[0] ?? null;
        $toStopId = $tripStopIds[count($tripStopIds) - 1] ?? null;
        $selectedStopIds = array_slice($tripStopIds, 1, -1);
        $selectedVehicleSummary = $selectedVehicle ? sprintf('Total: %d • Available: %d', max(1, (int) ($selectedVehicle->total_seats ?? 1)), max(1, (int) ($selectedVehicle->available_seats ?? 1))) : '';

        return view('admin.trips.edit', compact('trip', 'drivers', 'vehicles', 'routes', 'cities', 'citiesData', 'fromStopId', 'toStopId', 'selectedStopIds', 'driverVehicleMap', 'selectedVehicleId', 'selectedVehicleCapacity', 'selectedVehicleSummary'));
    }

    public function update(Request $request, DriverTrip $trip)
    {
        $request->merge([
            'selected_stop_ids' => $this->normalizeSelectedStopIds($request->input('selected_stop_ids')),
        ]);

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'trip_date' => ['nullable', 'date'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'available_seats' => ['required', 'integer', 'min:1', 'lte:total_seats'],
            'from_stop_id' => ['required', 'integer', 'exists:city_stops,id'],
            'to_stop_id' => ['required', 'integer', 'exists:city_stops,id', 'different:from_stop_id'],
            'selected_stop_ids' => ['nullable', 'array'],
            'selected_stop_ids.*' => ['integer', 'exists:city_stops,id'],
            'is_instant' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['scheduled', 'started', 'completed', 'cancelled'])],
        ]);

        $this->validateTripVehicleSelection($validated);
        $this->tripService->updateTrip($trip, $validated);

        return redirect()->route('admin.trips.show', $trip)->with('success', 'Trip updated successfully.');
    }

    public function destroy(DriverTrip $trip)
    {
        $this->tripService->deleteTrip($trip);

        return redirect()->route('admin.trips.index')->with('success', 'Trip deleted successfully.');
    }

    protected function buildCitiesData($cities): array
    {
        return $cities->map(function (City $city): array {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'stops' => $city->cityStops->map(function (CityStop $stop) use ($city): array {
                    return [
                        'id' => $stop->id,
                        'city_id' => $city->id,
                        'city_name' => $city->name,
                        'label' => $stop->location_name ?: $stop->address ?: $city->name,
                        'address' => $stop->address,
                        'latitude' => $stop->latitude,
                        'longitude' => $stop->longitude,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    protected function normalizeSelectedStopIds($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        if (is_string($value) && trim($value) !== '') {
            return array_values(array_filter(array_map('intval', preg_split('/[\s,]+/', trim($value)) ?: [])));
        }

        return [];
    }

    protected function buildDriverVehicleMap($drivers): array
    {
        $map = [];

        foreach ($drivers as $driver) {
            $vehicle = $this->getApprovedVehicleForDriver($driver);
            $map[$driver->id] = $vehicle ? [
                'id' => $vehicle->id,
                'label' => trim(sprintf('%s %s (%s)', $vehicle->brand ?? '', $vehicle->model ?? '', $vehicle->registration_number ?? '')),
                'total_seats' => max(1, (int) ($vehicle->total_seats ?? 1)),
                'available_seats' => max(1, (int) ($vehicle->available_seats ?? 1)),
            ] : null;
        }

        return $map;
    }

    protected function getApprovedVehicleForDriver(Driver $driver): ?Vehicle
    {
        return $driver->vehicles()
                        ->where('driver_id', $driver->id)
                        ->where('status', VehicleStatus::APPROVED)
                        ->orderByDesc('approved_at')
                        ->orderByDesc('id')
                        ->first();
    }

    protected function validateTripVehicleSelection(array $data): void
    {
        $driver = Driver::find($data['driver_id']);
        $vehicle = Vehicle::find($data['vehicle_id']);

        if (!$driver) {
            throw ValidationException::withMessages(['driver_id' => 'The selected driver could not be found.']);
        }

        if (!$vehicle) {
            throw ValidationException::withMessages(['vehicle_id' => 'The selected vehicle could not be found.']);
        }

        if ($vehicle->driver_id !== $driver->id) {
            throw ValidationException::withMessages(['vehicle_id' => 'The selected vehicle does not belong to the selected driver.']);
        }

        if ($vehicle->status !== VehicleStatus::APPROVED) {
            throw ValidationException::withMessages(['vehicle_id' => 'Only approved vehicles can be assigned to trips.']);
        }

        $maxSeats = max(1, (int) ($vehicle->available_seats ?? 1));
        $totalSeats = (int) ($data['total_seats'] ?? 0);
        $availableSeats = (int) ($data['available_seats'] ?? 0);

        if ($totalSeats > $maxSeats) {
            throw ValidationException::withMessages(['total_seats' => 'Total seats cannot exceed the approved vehicle capacity (' . $maxSeats . ').']);
        }

        if ($availableSeats < 1) {
            throw ValidationException::withMessages(['available_seats' => 'Available seats must be at least 1.']);
        }

        if ($availableSeats > $maxSeats) {
            throw ValidationException::withMessages(['available_seats' => 'Available seats cannot exceed the approved vehicle capacity (' . $maxSeats . ').']);
        }

        if ($availableSeats > $totalSeats) {
            throw ValidationException::withMessages(['available_seats' => 'Available seats cannot exceed total seats.']);
        }
    }
}
