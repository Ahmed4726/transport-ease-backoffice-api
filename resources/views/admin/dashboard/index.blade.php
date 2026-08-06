@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6">{{ now()->format('d M Y') }}</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row gy-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Total Drivers</small>
                    <h2 class="fw-bold mt-3">{{ $totalDrivers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Total Vehicles</small>
                    <h2 class="fw-bold mt-3">{{ $totalVehicles }}</h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Total Passengers</small>
                    <h2 class="fw-bold mt-3">{{ $totalPassengers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Total Trips</small>
                    <h2 class="fw-bold mt-3">{{ $totalTrips }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted mb-3">Driver Status Breakdown</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Approved</span>
                            <span class="badge bg-success">{{ $driverStatusCounts['approved'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Pending</span>
                            <span class="badge bg-warning text-dark">{{ $driverStatusCounts['pending'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Rejected</span>
                            <span class="badge bg-danger">{{ $driverStatusCounts['rejected'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="ratio ratio-4x3">
                        <canvas id="driverStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted mb-3">Vehicle Status Breakdown</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Approved</span>
                            <span class="badge bg-success">{{ $vehicleStatusCounts['approved'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Pending</span>
                            <span class="badge bg-warning text-dark">{{ $vehicleStatusCounts['pending'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Rejected</span>
                            <span class="badge bg-danger">{{ $vehicleStatusCounts['rejected'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="ratio ratio-4x3">
                        <canvas id="vehicleStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted mb-3">Quick Links</h6>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.drivers.index') }}" class="list-group-item list-group-item-action">Drivers</a>
                        <a href="{{ route('admin.vehicles.index') }}" class="list-group-item list-group-item-action">Vehicles</a>
                        <a href="{{ route('admin.passengers.index') }}" class="list-group-item list-group-item-action">Passengers</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Driver Registrations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDrivers as $driver)
                                    @php
                                        $driverStatus = $driver->status instanceof \App\Enums\UserStatus
                                            ? $driver->status->value
                                            : $driver->status;
                                    @endphp
                                    <tr>
                                        <td>{{ $driver->name }}</td>
                                        <td>{{ $driver->email }}</td>
                                        <td>{{ $driver->phone }}</td>
                                        <td>
                                            @if($driverStatus === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($driverStatus === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($driverStatus === 'pending' && $driver->driver)
                                                <a href="{{ route('admin.drivers.show', $driver->driver) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No drivers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const driverData = {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $driverStatusCounts['approved'] ?? 0 }},
                    {{ $driverStatusCounts['pending'] ?? 0 }},
                    {{ $driverStatusCounts['rejected'] ?? 0 }}
                ],
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                hoverOffset: 6,
            }]
        };

        const vehicleData = {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $vehicleStatusCounts['approved'] ?? 0 }},
                    {{ $vehicleStatusCounts['pending'] ?? 0 }},
                    {{ $vehicleStatusCounts['rejected'] ?? 0 }}
                ],
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                hoverOffset: 6,
            }]
        };

        const driverCtx = document.getElementById('driverStatusChart');
        if (driverCtx) {
            new Chart(driverCtx, {
                type: 'doughnut',
                data: driverData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const vehicleCtx = document.getElementById('vehicleStatusChart');
        if (vehicleCtx) {
            new Chart(vehicleCtx, {
                type: 'doughnut',
                data: vehicleData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
</script>
@endsection
