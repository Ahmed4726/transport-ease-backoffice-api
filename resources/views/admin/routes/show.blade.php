@extends('admin.layouts.app')

@section('title', 'Route Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Route: {{ $route->name }}</h2>
            <p class="text-muted">Manage stops and fares for this route.</p>
        </div>
        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">Back to routes</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Stops</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.routes.stops.store', $route) }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <select name="city_id" class="form-select" required>
                                    @foreach(App\Models\City::orderBy('name')->get() as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search stop location</label>
                                <input id="route-stop-search" name="location_name" type="text" class="form-control" placeholder="Type to search address">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <input id="route-stop-address" name="address" type="text" class="form-control" placeholder="Full address">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Latitude</label>
                                <input id="route-stop-latitude" name="latitude" type="text" class="form-control" placeholder="Latitude">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Longitude</label>
                                <input id="route-stop-longitude" name="longitude" type="text" class="form-control" placeholder="Longitude">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Place id</label>
                                <input id="route-stop-place-id" name="google_place_id" type="text" class="form-control" placeholder="Google Place ID">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stop order</label>
                                <input type="number" name="stop_order" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Distance (km)</label>
                                <input type="number" step="0.1" name="distance_from_start" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Minutes</label>
                                <input type="number" name="estimated_minutes" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Add Stop</button>
                        </div>
                    </form>
                    <div id="route-stop-map" class="mt-3" style="height: 300px; border: 1px solid #dee2e6; border-radius: .375rem;"></div>
                    <p class="small text-muted mt-2">Search the map or click a location to populate stop coordinates.</p>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>City</th>
                                    <th>Order</th>
                                    <th>Distance</th>
                                    <th>Minutes</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($route->stops as $stop)
                                    <tr>
                                        <td>{{ $stop->id }}</td>
                                        <td>{{ $stop->city?->name }}</td>
                                        <td>{{ $stop->stop_order }}</td>
                                        <td>{{ $stop->distance_from_start }}</td>
                                        <td>{{ $stop->estimated_minutes }}</td>
                                        <td>
                                            <a href="{{ route('admin.routes.stops.edit', [$route, $stop]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="{{ route('admin.routes.stops.destroy', [$route, $stop]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete stop?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Fares</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.routes.fares.store', $route) }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">From stop</label>
                                <select name="from_stop_id" class="form-select" required>
                                    @foreach($route->stops as $stop)
                                        <option value="{{ $stop->id }}">{{ $stop->city?->name }} ({{ $stop->stop_order }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">To stop</label>
                                <select name="to_stop_id" class="form-select" required>
                                    @foreach($route->stops as $stop)
                                        <option value="{{ $stop->id }}">{{ $stop->city?->name }} ({{ $stop->stop_order }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fare</label>
                                <input type="number" step="0.01" name="fare" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100">Add Fare</button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Fare</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($route->fares as $fare)
                                    <tr>
                                        <td>{{ $fare->id }}</td>
                                        <td>{{ $fare->fromStop?->city?->name }}</td>
                                        <td>{{ $fare->toStop?->city?->name }}</td>
                                        <td>{{ $fare->fare }}</td>
                                        <td>
                                            <a href="{{ route('admin.routes.fares.edit', [$route, $fare]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="{{ route('admin.routes.fares.destroy', [$route, $fare]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete fare?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
