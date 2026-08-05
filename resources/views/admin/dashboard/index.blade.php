@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Dashboard
            </h2>

            <p class="text-muted mb-0">
                Welcome back, {{ auth()->user()->name }}
            </p>

        </div>

        <div>

            <span class="badge bg-primary fs-6">
                {{ now()->format('d M Y') }}
            </span>

        </div>

    </div>

    <!-- Statistics -->

    <div class="row">

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total Drivers
                            </small>

                            <h2 class="fw-bold mt-2">
                                {{ $totalDrivers }}
                            </h2>

                        </div>

                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;">

                            <i class="bi bi-person-badge fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Passengers
                            </small>

                            <h2 class="fw-bold mt-2">
                                {{ $totalPassengers }}
                            </h2>

                        </div>

                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;">

                            <i class="bi bi-people-fill fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Pending Drivers
                            </small>

                            <h2 class="fw-bold mt-2">
                                {{ $pendingDrivers }}
                            </h2>

                        </div>

                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;">

                            <i class="bi bi-clock-history fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card border-0 shadow h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Vehicles
                            </small>

                            <h2 class="fw-bold mt-2">
                                0
                            </h2>

                        </div>

                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;">

                            <i class="bi bi-bus-front-fill fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Recent Drivers -->

    <div class="card shadow border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                Recent Driver Registrations

            </h5>

        </div>

        <div class="card-body">

            <table class="table table-hover align-middle">

                <thead>

                <tr>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Phone</th>

                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                @forelse($recentDrivers as $driver)

                    <tr>

                        <td>{{ $driver->name }}</td>

                        <td>{{ $driver->email }}</td>

                        <td>{{ $driver->phone }}</td>

                        <td>

                            @if($driver->status->value == 'approved')

                                <span class="badge bg-success">
                                    Approved
                                </span>

                            @elseif($driver->status->value == 'pending')

                                <span class="badge bg-warning">
                                    Pending
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Rejected
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center">

                            No drivers found.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
