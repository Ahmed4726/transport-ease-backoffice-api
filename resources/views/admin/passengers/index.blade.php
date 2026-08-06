@extends('admin.layouts.app')

@section('title', 'Passengers')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">Passengers</h2>

            <p class="text-muted">Manage all passengers.</p>

        </div>

        <a href="{{ route('admin.passengers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Passenger
        </a>

    </div>

    <div class="row mb-4">

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Total Passengers</small>
                    <h2 class="fw-bold mt-2">{{ $summary['totalPassengers'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Approved</small>
                    <h2 class="fw-bold text-success mt-2">{{ $summary['approvedPassengers'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Pending</small>
                    <h2 class="fw-bold text-warning mt-2">{{ $summary['pendingPassengers'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Rejected</small>
                    <h2 class="fw-bold text-danger mt-2">{{ $summary['rejectedPassengers'] }}</h2>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search passengers..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.passengers.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th width="140">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($passengers as $passenger)
                    <tr>
                        <td>{{ $loop->iteration + ($passengers->currentPage() - 1) * $passengers->perPage() }}</td>
                        <td>{{ $passenger->user->name }}</td>
                        <td>{{ $passenger->user->email }}</td>
                        <td>{{ $passenger->user->phone }}</td>
                        <td>
                            @if($passenger->user->status->value === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($passenger->user->status->value === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ optional($passenger->created_at)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.passengers.show', $passenger) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.passengers.edit', $passenger) }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.passengers.destroy', $passenger) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this passenger?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            No passengers found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-end">{{ $passengers->links() }}</div>
        </div>
    </div>

</div>

@endsection
