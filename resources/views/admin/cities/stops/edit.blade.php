@extends('admin.layouts.app')

@section('title', 'Edit City Stop')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Edit Stop for {{ $city->name }}</h2>
            <p class="text-muted">Update stop location details.</p>
        </div>
        <a href="{{ route('admin.cities.stops.index', $city) }}" class="btn btn-secondary">Back to stops</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cities.stops.update', [$city, $stop]) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Stop name</label>
                        <input id="city-stop-name" type="text" name="location_name" class="form-control" value="{{ old('location_name', $stop->location_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search location</label>
                        <input id="city-stop-search" name="location_search" type="text" class="form-control" placeholder="Type to search address">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <input id="city-stop-address" name="address" type="text" class="form-control" placeholder="Full address" value="{{ old('address', $stop->address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input id="city-stop-latitude" name="latitude" type="text" class="form-control" value="{{ old('latitude', $stop->latitude) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input id="city-stop-longitude" name="longitude" type="text" class="form-control" value="{{ old('longitude', $stop->longitude) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Place id</label>
                        <input id="city-stop-place-id" name="google_place_id" type="text" class="form-control" value="{{ old('google_place_id', $stop->google_place_id) }}">
                    </div>
                    <div class="col-md-12">
                        <div id="city-stop-map" style="height: 360px; border: 1px solid #dee2e6; border-radius: .375rem;"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Update Stop</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
