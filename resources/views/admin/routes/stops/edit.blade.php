@extends('admin.layouts.app')

@section('title', 'Edit Route Stop')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Edit Stop</h2>
            <p class="text-muted">Update stop details for route {{ $route->name }}.</p>
        </div>
        <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.routes.stops.update', [$route, $stop]) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" value="{{ $stop->city?->name }}" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stop order</label>
                    <input type="number" name="stop_order" class="form-control" value="{{ old('stop_order', $stop->stop_order) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Distance (km)</label>
                    <input type="number" step="0.1" name="distance_from_start" class="form-control" value="{{ old('distance_from_start', $stop->distance_from_start) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Estimated minutes</label>
                    <input type="number" name="estimated_minutes" class="form-control" value="{{ old('estimated_minutes', $stop->estimated_minutes) }}">
                </div>
                <button class="btn btn-primary">Save stop</button>
            </form>
        </div>
    </div>
</div>
@endsection
