@extends('admin.layouts.app')

@section('title', 'Routes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Routes</h2>
            <p class="text-muted">View routes and manage stops/fares.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Distance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $route)
                            <tr>
                                <td>{{ $route->id }}</td>
                                <td>{{ $route->name }}</td>
                                <td>{{ $route->startCity?->name }}</td>
                                <td>{{ $route->endCity?->name }}</td>
                                <td>{{ $route->distance }} km</td>
                                <td>{{ $route->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-sm btn-primary">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
