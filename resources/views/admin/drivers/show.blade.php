@extends('admin.layouts.app')

@section('title', 'Driver Details')

@section('content')

<div class="container-fluid">

    <div class="row">

        <!-- Left Column -->

        <div class="col-lg-4">

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-body text-center">

                    @if($driver->profile_photo)
                        <img
                            src="{{ asset('storage/'.$driver->profile_photo) }}"
                            class="rounded-circle mb-3"
                            width="120"
                            height="120"
                            alt="Profile Photo">
                    @else
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($driver->user->name) }}&background=0D6EFD&color=fff&size=200"
                            class="rounded-circle mb-3"
                            width="120"
                            height="120"
                            alt="Profile Avatar">
                    @endif

                    <h4>

                        {{ $driver->user->name }}

                    </h4>

                    <p class="text-muted">

                        {{ $driver->user->email }}

                    </p>

                    <p class="mb-2">
                        <strong>Phone:</strong> {{ $driver->user->phone }}
                    </p>

                    @if($driver->user->status === \App\Enums\UserStatus::APPROVED)

                        <span class="badge bg-success">

                            Approved

                        </span>

                    @elseif($driver->user->status === \App\Enums\UserStatus::PENDING)

                        <span class="badge bg-warning text-dark">

                            Pending

                        </span>

                    @else

                        <span class="badge bg-danger">

                            Rejected

                        </span>

                    @endif

                </div>

            </div>

    <div class="card shadow-sm border-0">

        <div class="card-header">

            Review

        </div>

        <div class="card-body">

            @if($driver->user->status === \App\Enums\UserStatus::PENDING)

                <form
                    action="{{ route('admin.drivers.approve', $driver) }}"
                    method="POST">

                    @csrf

                    <button
                        class="btn btn-success w-100 mb-3">

                        <i class="bi bi-check-circle"></i>

                        Approve Driver

                    </button>

                </form>

                <button
                    class="btn btn-danger w-100"
                    data-bs-toggle="modal"
                    data-bs-target="#rejectModal">

                    Reject Driver

                </button>

            @elseif($driver->user->status === \App\Enums\UserStatus::APPROVED)

                <div class="alert alert-success mb-0">

                    <h6 class="mb-2">
                        <i class="bi bi-check-circle-fill"></i>
                        Driver Approved
                    </h6>

                    <p class="mb-1">
                        <strong>Remark:</strong>
                    </p>

                    <p class="mb-2">
                        {{ $driver->remarks }}
                    </p>

                    @if($driver->approvedBy)
                        <small class="text-muted">
                            Approved by <strong>{{ $driver->approvedBy->name }}</strong><br>
                            {{ optional($driver->approved_at)->format('d M Y h:i A') }}
                        </small>
                    @endif

                </div>

            @elseif($driver->user->status === \App\Enums\UserStatus::REJECTED)

                <div class="alert alert-danger mb-0">

                    <h6 class="mb-2">
                        <i class="bi bi-x-circle-fill"></i>
                        Driver Rejected
                    </h6>

                    <p class="mb-1">
                        <strong>Reason:</strong>
                    </p>

                    <p class="mb-2">
                        {{ $driver->remarks }}
                    </p>

                    @if($driver->rejectedBy)
                        <small class="text-muted">
                            Rejected by <strong>{{ $driver->rejectedBy->name }}</strong><br>
                            {{ optional($driver->rejected_at)->format('d M Y h:i A') }}
                        </small>
                    @endif

                </div>

            @endif

        </div>

    </div>

        </div>

        <!-- Right Column -->

        <div class="col-lg-8">

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header">

                    Driver Information

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <strong>CNIC</strong>

                            <br>

                            {{ $driver->cnic ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Licence Number</strong>

                            <br>

                            {{ $driver->license_number ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Licence Expiry</strong>

                            <br>

                            {{ optional($driver->license_expiry)->format('d M Y') ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Address</strong>

                            <br>

                            {{ $driver->address ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>City</strong>

                            <br>

                            {{ $driver->city ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Date of Birth</strong>

                            <br>

                            {{ $driver->date_of_birth ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Blood Group</strong>

                            <br>

                            {{ $driver->blood_group ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Emergency Contact</strong>

                            <br>

                            {{ $driver->emergency_contact_name ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Emergency Phone</strong>

                            <br>

                            {{ $driver->emergency_contact_phone ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Completed Trips</strong>

                            <br>

                            {{ $driver->completed_trips ?? 0 }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Rating</strong>

                            <br>

                            {{ number_format($driver->rating ?? 0, 2) }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Availability</strong>

                            <br>

                            @if($driver->is_available)
                                Available
                            @else
                                Not Available
                            @endif

                        </div>

                    </div>

                </div>

            </div>

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header">

                    Additional Information

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <strong>Created At</strong>

                            <br>

                            {{ optional($driver->created_at)->format('d M Y h:i A') ?? '-' }}

                        </div>

                        <div class="col-md-6 mb-3">

                            <strong>Last Updated</strong>

                            <br>

                            {{ optional($driver->updated_at)->format('d M Y h:i A') ?? '-' }}

                        </div>


                    </div>

                </div>

            </div>

            <div class="card shadow-sm border-0">

                <div class="card-header">

                    Uploaded Documents

                </div>

                <div class="card-body">

                    <div class="row">

                        @php
                            $documents = [
                                'Profile Photo' => $driver->profile_photo,
                                'CNIC Front' => $driver->cnic_front,
                                'CNIC Back' => $driver->cnic_back,
                                'Licence Front' => $driver->license_front,
                                'Licence Back' => $driver->license_back,
                            ];
                        @endphp

                        @foreach($documents as $label => $path)
                            <div class="col-md-4 mb-4">
                                <div class="card border-light h-100">
                                    <div class="card-body text-center">
                                        @if($path)
                                            <button
                                                type="button"
                                                class="btn btn-link p-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#documentModal{{ $loop->index }}">
                                                <img
                                                    src="{{ asset('storage/'.$path) }}"
                                                    class="img-fluid rounded mb-2"
                                                    style="max-height: 160px; object-fit: cover; width: 100%;"
                                                    alt="{{ $label }}">
                                            </button>
                                            <p class="mb-0"><strong>{{ $label }}</strong></p>
                                        @else
                                            <div class="border rounded p-4 text-muted">
                                                No {{ strtolower($label) }} uploaded.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                </div>

            </div>

            @foreach($documents as $label => $path)
                @if($path)
                    <div class="modal fade" id="documentModal{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        {{ $label }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img
                                        src="{{ asset('storage/'.$path) }}"
                                        class="img-fluid"
                                        alt="{{ $label }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

        </div>

    </div>

</div>

<!-- Reject Modal -->

<div
    class="modal fade"
    id="rejectModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form
                action="{{ route('admin.drivers.reject',$driver) }}"
                method="POST">

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
                                    id="issue-{{ $key }}">
                                <label class="form-check-label" for="issue-{{ $key }}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <textarea
                        name="remarks"
                        rows="5"
                        class="form-control"
                        placeholder="Enter rejection reason..."
                        required></textarea>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        class="btn btn-danger">

                        Reject Driver

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
