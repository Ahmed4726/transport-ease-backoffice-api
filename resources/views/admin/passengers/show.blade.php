@extends('admin.layouts.app')

@section('title', 'Passenger Details')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Passenger Details</h2>
            <p class="text-muted">View passenger profile.</p>
        </div>
        <div>
            <a href="{{ route('admin.passengers.edit', $passenger) }}" class="btn btn-secondary me-2">Edit</a>
            <a href="{{ route('admin.passengers.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Name</strong>
                            <div>{{ $passenger->user->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email</strong>
                            <div>{{ $passenger->user->email }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phone</strong>
                            <div>{{ $passenger->user->phone }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status</strong>
                            <div>
                                @if($passenger->user->status === \App\Enums\UserStatus::APPROVED)
                                    <span class="badge bg-success">Approved</span>
                                @elseif($passenger->user->status === \App\Enums\UserStatus::PENDING)
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Created At</small>
                            <div>{{ optional($passenger->created_at)->format('d M Y h:i A') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Last Updated</small>
                            <div>{{ optional($passenger->updated_at)->format('d M Y h:i A') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
