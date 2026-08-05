@extends('admin.layouts.app')

@section('title', 'City Stops')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Stops for {{ $city->name }}</h2>
            <p class="text-muted">Add precise stop locations for this city.</p>
        </div>
        <a href="{{ route('admin.cities.stops.create', $city) }}" class="btn btn-primary">Add Stop</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stops as $stop)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $stop->location_name }}</td>
                                <td>{{ $stop->address }}</td>
                                <td>{{ $stop->latitude }}</td>
                                <td>{{ $stop->longitude }}</td>
                                <td>
                                    <a href="{{ route('admin.cities.stops.edit', [$city, $stop]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.cities.stops.destroy', [$city, $stop]) }}" method="POST" class="d-inline">
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
@endsection
