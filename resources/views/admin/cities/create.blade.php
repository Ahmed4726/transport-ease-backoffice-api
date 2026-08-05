@extends('admin.layouts.app')

@section('title', 'Add City')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Add City</h2>
            <p class="text-muted">Create a new city and select its coordinates on the map.</p>
        </div>
        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">Back to cities</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cities.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">City Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" class="form-control" value="{{ old('province') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Search location</label>
                        <input id="city-search" name="location_search" type="text" class="form-control" placeholder="Search city location">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input id="city-latitude" type="text" name="latitude" class="form-control" value="{{ old('latitude') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input id="city-longitude" type="text" name="longitude" class="form-control" value="{{ old('longitude') }}">
                    </div>
                    <div class="col-md-12">
                        <div id="city-map" style="height: 320px; border: 1px solid #dee2e6; border-radius: .375rem;"></div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="city-is-active" checked>
                            <label class="form-check-label" for="city-is-active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Save City</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
