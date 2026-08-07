@extends('admin.layouts.app')

@section('title', 'Vehicle Management')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Vehicle Management
            </h3>

            <p class="text-muted mb-0">
                Manage all registered vehicles
            </p>

        </div>

        <div>
            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Vehicle
            </a>
        </div>

    </div>

    {{-- Dashboard Cards --}}
    <div class="row">

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total Vehicles
                            </small>

                            <h2 class="fw-bold mt-2">
                                {{ $summary['totalVehicles'] }}
                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-truck fs-1 text-primary"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Pending
                            </small>

                            <h2 class="fw-bold mt-2 text-warning">

                                {{ $summary['pendingVehicles'] }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-hourglass-split fs-1 text-warning"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Approved
                            </small>

                            <h2 class="fw-bold mt-2 text-success">

                                {{ $summary['approvedVehicles'] }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-check-circle fs-1 text-success"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Rejected
                            </small>

                            <h2 class="fw-bold mt-2 text-danger">

                                {{ $summary['rejectedVehicles'] }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-x-circle fs-1 text-danger"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Search Card --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-5">

                        <label class="form-label">

                            Search

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            placeholder="Driver, Email, Brand, Model..."
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option
                                value="pending"
                                {{ request('status')=='pending' ? 'selected':'' }}>

                                Pending

                            </option>

                            <option
                                value="approved"
                                {{ request('status')=='approved' ? 'selected':'' }}>

                                Approved

                            </option>

                            <option
                                value="rejected"
                                {{ request('status')=='rejected' ? 'selected':'' }}>

                                Rejected

                            </option>

                        </select>

                    </div>

                    <div class="col-md-4 d-flex align-items-end">

                        <button
                            class="btn btn-primary me-2">

                            <i class="bi bi-search"></i>

                            Search

                        </button>

                        <a
                            href="{{ route('admin.vehicles.index') }}"
                            class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Vehicles Table --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                Vehicle List

            </h5>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                <tr>

                    <th>#</th>

                    <th>Driver</th>

                    <th>Vehicle</th>

                    <th>Registration</th>

                    <th>Seats</th>

                    <th>Status</th>

                    <th>Submitted</th>

                    <th width="140">
                        Action
                    </th>

                </tr>

                </thead>

                <tbody>

            @forelse($vehicles as $vehicle)

@php
    $driverUser = $vehicle->driver?->user;
    $driverName = optional($driverUser)->name ?? 'Unknown Driver';
    $driverEmail = optional($driverUser)->email ?? 'No email';
    $vehicleBrand = $vehicle->brand ?? 'Unknown';
    $vehicleModel = $vehicle->model ?? 'Vehicle';
    $vehicleType = optional($vehicle->vehicleType)->name ?? 'Unknown type';
    $registrationNumber = $vehicle->registration_number ?? 'N/A';
    $availableSeats = $vehicle->available_seats ?? 0;
    $totalSeats = $vehicle->total_seats ?? 0;
@endphp

<tr>

    <td>

        {{ $vehicle->id }}

    </td>

    <td>

        <div class="fw-bold">

            {{ $driverName }}

        </div>

        <small class="text-muted">

            {{ $driverEmail }}

        </small>

    </td>

    <td>

        <div class="fw-bold">

            {{ $vehicleBrand }} {{ $vehicleModel }}

        </div>

        <small class="text-muted">

            {{ $vehicleType }}

        </small>

    </td>

    <td>

        {{ $registrationNumber }}

    </td>

    <td>

        {{ $availableSeats }} / {{ $totalSeats }}

    </td>

    <td>

        @if($vehicle->status === \App\Enums\VehicleStatus::PENDING)

            <span class="badge bg-warning">

                Pending

            </span>

        @elseif($vehicle->status === \App\Enums\VehicleStatus::APPROVED)

            <span class="badge bg-success">

                Approved

            </span>

        @else

            <span class="badge bg-danger">

                Rejected

            </span>

        @endif

    </td>

    <td>

        {{ $vehicle->created_at->format('d M Y') }}

        <br>

        <small class="text-muted">

            {{ $vehicle->created_at->format('h:i A') }}

        </small>

    </td>

    <td class="text-nowrap">

        <a
            href="{{ route('admin.vehicles.show', $vehicle) }}"
            class="btn btn-primary btn-sm me-1">

            <i class="bi bi-eye"></i>

            View

        </a>

        <a
            href="{{ route('admin.vehicles.edit', $vehicle) }}"
            class="btn btn-outline-secondary btn-sm">

            <i class="bi bi-pencil-square"></i>

            Edit

        </a>

    </td>

</tr>

@empty

<tr>

    <td colspan="8">

        <div class="text-center py-5">

            <i class="bi bi-truck display-3 text-secondary"></i>

            <h5 class="mt-3">

                No Vehicles Found

            </h5>

            <p class="text-muted mb-0">

                There are no registered vehicles.

            </p>

        </div>

    </td>

</tr>

@endforelse

</tbody>

</table>

</div>

<div class="card-footer bg-white">

    <div class="d-flex justify-content-end">

        {{ $vehicles->links() }}

    </div>

</div>

</div>

</div>

@endsection
