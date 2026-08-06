<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\User;
use App\Services\Admin\PassengerService;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PassengerController extends Controller
{
    protected PassengerService $passengerService;

    public function __construct(PassengerService $passengerService)
    {
        $this->passengerService = $passengerService;
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $passengers = $this->passengerService->getPassengers($filters);
        $summary = $this->passengerService->getSummary();

        return view('admin.passengers.index', compact(
            'passengers',
            'filters',
            'summary'
        ));
    }

    public function create()
    {
        return view('admin.passengers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in([UserStatus::APPROVED->value, UserStatus::PENDING->value, UserStatus::REJECTED->value])],
        ]);

        $this->passengerService->createPassenger($validated);

        return redirect()
            ->route('admin.passengers.index')
            ->with('success', 'Passenger created successfully.');
    }

    public function show(Passenger $passenger)
    {
        $passenger = $this->passengerService->getPassenger($passenger->id);

        return view('admin.passengers.show', compact('passenger'));
    }

    public function edit(Passenger $passenger)
    {
        return view('admin.passengers.edit', compact('passenger'));
    }

    public function update(Request $request, Passenger $passenger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($passenger->user_id)],
            'phone' => ['required', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($passenger->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in([UserStatus::APPROVED->value, UserStatus::PENDING->value, UserStatus::REJECTED->value])],
        ]);

        $this->passengerService->updatePassenger($passenger, $validated);

        return redirect()
            ->route('admin.passengers.show', $passenger)
            ->with('success', 'Passenger updated successfully.');
    }

    public function destroy(Passenger $passenger)
    {
        $this->passengerService->deletePassenger($passenger);

        return redirect()
            ->route('admin.passengers.index')
            ->with('success', 'Passenger deleted successfully.');
    }
}
