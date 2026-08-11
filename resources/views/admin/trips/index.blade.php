@extends('admin.layouts.app')

@section('title', 'Trips')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Trips</h2>
            <p class="text-muted">Manage driver trips and statuses.</p>
        </div>
        <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">Create Trip</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="row g-3 mb-4" method="GET" action="{{ route('admin.trips.index') }}">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search drivers, vehicles, registration...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="instant" {{ ($filters['status'] ?? '') === 'instant' ? 'selected' : '' }}>Instant</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Route</th>
                            <th>Stops / Distance / ETA</th>
                            <th>Status</th>
                            <th>Seats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                            <tr>
                                <td>{{ $trip->driver->user->name ?? 'N/A' }}</td>
                                <td>{{ $trip->vehicle->brand ?? 'N/A' }} {{ $trip->vehicle->model ?? '' }}<br><small>{{ $trip->vehicle->registration_number ?? '' }}</small></td>
                                <td>
                                    @php
                                        $orderedStops = $trip->stops->sortBy('stop_order')->values();
                                        $fromCity = $orderedStops->first()?->stop?->city?->name ?? 'N/A';
                                        $toCity = $orderedStops->last()?->stop?->city?->name ?? 'N/A';
                                        $stopCount = max(2, $orderedStops->count());

                                        $routeStops = $orderedStops
                                            ->map(fn ($tripStop) => [
                                                'lat' => (float) ($tripStop->stop?->latitude ?? 0),
                                                'lng' => (float) ($tripStop->stop?->longitude ?? 0),
                                            ])
                                            ->filter(fn ($stop) => $stop['lat'] != 0 && $stop['lng'] != 0)
                                            ->values();

                                        $distanceKm = 0;
                                        if ($routeStops->count() >= 2) {
                                            $earthRadiusKm = 6371;
                                            $toRad = fn ($value) => ($value * M_PI) / 180;

                                            for ($i = 0; $i < $routeStops->count() - 1; $i++) {
                                                $start = $routeStops[$i];
                                                $end = $routeStops[$i + 1];
                                                $lat1 = $toRad($start['lat']);
                                                $lon1 = $toRad($start['lng']);
                                                $lat2 = $toRad($end['lat']);
                                                $lon2 = $toRad($end['lng']);
                                                $deltaLat = $lat2 - $lat1;
                                                $deltaLon = $lon2 - $lon1;
                                                $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
                                                $distanceKm += $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
                                            }
                                        }

                                        $etaMinutes = $distanceKm > 0 ? max(20, (int) round(($distanceKm / 35) * 60)) : max(20, ($stopCount - 1) * 20);
                                    @endphp
                                    <div>{{ $fromCity }} → {{ $toCity }}</div>
                                    <small class="text-muted">{{ $trip->route->name ?? 'Instant route' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">Stops: {{ $stopCount }}</div>
                                    <div class="small text-muted">Distance: {{ number_format($distanceKm, 1) }} km</div>
                                    <div class="small text-muted">ETA: {{ $etaMinutes }} min</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Instant</span>
                                </td>
                                <td>{{ $trip->available_seats }} / {{ $trip->total_seats }}</td>
                                <td>
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('admin.trips.destroy', $trip) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this trip?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No trips found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $trips->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
