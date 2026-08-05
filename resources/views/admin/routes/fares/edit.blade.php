@extends('admin.layouts.app')

@section('title', 'Edit Route Fare')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Edit Fare</h2>
            <p class="text-muted">Update fare details for route {{ $route->name }}.</p>
        </div>
        <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.routes.fares.update', [$route, $fare]) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">From stop</label>
                    <select name="from_stop_id" class="form-select" required>
                        @foreach($stops as $stop)
                            <option value="{{ $stop->id }}" {{ $fare->from_stop_id === $stop->id ? 'selected' : '' }}>
                                {{ $stop->city?->name }} ({{ $stop->stop_order }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">To stop</label>
                    <select name="to_stop_id" class="form-select" required>
                        @foreach($stops as $stop)
                            <option value="{{ $stop->id }}" {{ $fare->to_stop_id === $stop->id ? 'selected' : '' }}>
                                {{ $stop->city?->name }} ({{ $stop->stop_order }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fare</label>
                    <input type="number" step="0.01" name="fare" class="form-control" value="{{ old('fare', $fare->fare) }}" required>
                </div>
                <button class="btn btn-primary">Save fare</button>
            </form>
        </div>
    </div>
</div>
@endsection
