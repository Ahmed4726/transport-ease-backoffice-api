@extends('admin.layouts.app')

@section('title', 'Cities')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Cities</h2>
            <p class="text-muted">Manage cities and their location data.</p>
        </div>
        <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">Add City</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Province</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $city)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->province }}</td>
                                <td>{{ $city->latitude }}</td>
                                <td>{{ $city->longitude }}</td>
                                <td>{{ $city->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="{{ route('admin.cities.stops.index', $city) }}" class="btn btn-sm btn-outline-secondary">Manage Stops</a>
                                    <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete city?')">Delete</button>
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
@endsection
