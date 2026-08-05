@extends('admin.layouts.app')

@section('title', 'Drivers')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">Drivers</h2>

            <p class="text-muted">
                Manage all registered drivers
            </p>

        </div>

    </div>

    <div class="card shadow border-0">

        <div class="card-body">

                    <form method="GET">

                        <div class="row mb-4">

                            <div class="col-md-4">

                                <input
                                    type="text"
                                    class="form-control"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search drivers...">

                            </div>

                            <div class="col-md-3">

                                <select
                                    name="status"
                                    class="form-select">

                                    <option value="">All Status</option>

                                    <option
                                        value="pending"
                                        {{ request('status')=='pending'?'selected':'' }}>

                                        Pending

                                    </option>

                                    <option
                                        value="approved"
                                        {{ request('status')=='approved'?'selected':'' }}>

                                        Approved

                                    </option>

                                    <option
                                        value="rejected"
                                        {{ request('status')=='rejected'?'selected':'' }}>

                                        Rejected

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-2">

                                <button
                                    class="btn btn-primary w-100">

                                    Search

                                </button>

                            </div>

                            <div class="col-md-2">

                                <a
                                    href="{{ route('admin.drivers.index') }}"
                                    class="btn btn-secondary w-100">

                                    Reset

                                </a>

                            </div>

                        </div>

                    </form>


            <div class="table-responsive">
            <div class="row mb-4">

                <div class="col-lg-3">

                    <a href="{{ route('admin.drivers.index') }}"
                    class="text-decoration-none">

                        <div class="card shadow-sm border-0">

                            <div class="card-body">

                                <h6>All Drivers</h6>

                                <h3>{{ $summary['totalDrivers'] }}</h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-lg-3">

                    <a href="{{ route('admin.drivers.index',['status'=>'pending']) }}"
                    class="text-decoration-none">

                        <div class="card border-warning shadow-sm">

                            <div class="card-body">

                                <h6>Pending</h6>

                                <h3>

                                    {{ $summary['pendingDrivers'] }}

                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-lg-3">

                    <a href="{{ route('admin.drivers.index',['status'=>'approved']) }}"
                    class="text-decoration-none">

                        <div class="card border-success shadow-sm">

                            <div class="card-body">

                                <h6>Approved</h6>

                                <h3>

                                    {{ $summary['approvedDrivers'] }}

                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-lg-3">

                    <a href="{{ route('admin.drivers.index',['status'=>'rejected']) }}"
                    class="text-decoration-none">

                        <div class="card border-danger shadow-sm">

                            <div class="card-body">

                                <h6>Rejected</h6>

                                <h3>

                                    {{ $summary['rejectedDrivers'] }}

                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

            </div>
                <table class="table table-hover align-middle">

                    <thead class="table-light">

                    <tr>

                        <th>#</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Licence</th>

                        <th>Expiry</th>

                        <th>Status</th>

                        <th width="220">Action</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($drivers as $driver)

                        <tr>

                            <td>

                                {{ $loop->iteration + ($drivers->currentPage() - 1) * $drivers->perPage() }}

                            </td>

                            <td>

                                {{ $driver->user->name }}

                            </td>

                            <td>

                                {{ $driver->user->email }}

                            </td>

                            <td>

                                {{ $driver->user->phone }}

                            </td>

                            <td>

                                {{ $driver->license_number ?? '-' }}

                            </td>

                            <td>

                                {{ optional($driver->license_expiry)->format('d M Y') ?? '-' }}

                            </td>

                            <td>

                                @if($driver->user->status->value=='approved')

                                    <span class="badge bg-success">

                                        Approved

                                    </span>

                                @elseif($driver->user->status->value=='pending')

                                    <span class="badge bg-warning text-dark">

                                        Pending

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Rejected

                                    </span>

                                @endif

                            </td>

                            <td>

                                <a
                                    href="{{ route('admin.drivers.show',$driver) }}"
                                    class="btn btn-primary btn-sm">

                                    <i class="bi bi-eye"></i>

                                </a>

                                @if($driver->user->status->value=='pending')

                                    <form
                                        action="{{ route('admin.drivers.approve',$driver) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button
                                            class="btn btn-success btn-sm">

                                            <i class="bi bi-check-lg"></i>

                                        </button>

                                    </form>

                                    <button
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal{{ $driver->id }}">

                                        <i class="bi bi-x-lg"></i>

                                    </button>

                                @endif

                            </td>

                        </tr>

                        <!-- Reject Modal -->

                        <div class="modal fade"
                             id="rejectModal{{ $driver->id }}"
                             tabindex="-1">

                            <div class="modal-dialog">

                                <div class="modal-content">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.drivers.reject',$driver) }}">

                                        @csrf

                                        <div class="modal-header">

                                            <h5>

                                                Reject Driver

                                            </h5>

                                        </div>

                                        <div class="modal-body">

                                            <div class="mb-3">
                                                <label class="form-label">Select issues</label>

                                                @foreach([
                                                    'profile_photo' => 'Profile photo',
                                                    'cnic_front' => 'CNIC front',
                                                    'cnic_back' => 'CNIC back',
                                                    'license_front' => 'License front',
                                                    'license_back' => 'License back',
                                                    'address' => 'Address',
                                                    'city' => 'City',
                                                    'date_of_birth' => 'Date of birth',
                                                    'emergency_contact_name' => 'Emergency contact name',
                                                    'emergency_contact_phone' => 'Emergency contact phone',
                                                    'blood_group' => 'Blood group',
                                                ] as $key => $label)
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="rejection_issues[]"
                                                            value="{{ $key }}"
                                                            id="issue-{{ $driver->id }}-{{ $key }}">
                                                        <label class="form-check-label" for="issue-{{ $driver->id }}-{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <textarea
                                                name="remarks"
                                                class="form-control"
                                                rows="5"
                                                placeholder="Reason..."
                                                required></textarea>

                                        </div>

                                        <div class="modal-footer">

                                            <button
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal"
                                                type="button">

                                                Cancel

                                            </button>

                                            <button
                                                class="btn btn-danger">

                                                Reject

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                                </div>

                            </div>

                        </div>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                No Drivers Found

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">

                {{ $drivers->links() }}

            </div>

        </div>

    </div>

</div>
<style>

.blink-btn{

    animation: blinkAnimation 1s infinite;

    font-weight:600;

}

@keyframes blinkAnimation{

    0%{

        opacity:1;

        transform:scale(1);

    }

    50%{

        opacity:.4;

        transform:scale(1.05);

    }

    100%{

        opacity:1;

        transform:scale(1);

    }

}

</style>
@endsection
