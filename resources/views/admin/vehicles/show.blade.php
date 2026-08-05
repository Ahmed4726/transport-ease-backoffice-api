@extends('admin.layouts.app')

@section('title', 'Vehicle Details')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                Vehicle Details

            </h3>

            <p class="text-muted mb-0">

                View complete vehicle information

            </p>

        </div>

        <a
            href="{{ route('admin.vehicles.index') }}"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Back

        </a>

    </div>

    <div class="row">

        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Driver Information --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        Driver Information

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Driver Name

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->driver->user->name }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Email

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->driver->user->email }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Phone Number

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->driver->user->phone }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Driver Status

                            </label>

                            <div>

                                @if($vehicle->driver->user->status === \App\Enums\UserStatus::APPROVED)

                                    <span class="badge bg-success">

                                        Approved

                                    </span>

                                @elseif($vehicle->driver->user->status === \App\Enums\UserStatus::PENDING)

                                    <span class="badge bg-warning">

                                        Pending

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Rejected

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Vehicle Information --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        Vehicle Information

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Vehicle Type

                            </label>

                            <div class="fw-bold">

                                {{ optional($vehicle->vehicleType)->name }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Brand

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->brand }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Model

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->model }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Manufacture Year

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->manufacture_year }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Color

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->color }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Registration Number

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->registration_number }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Engine Number

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->engine_number }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Chassis Number

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->chassis_number }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Total Seats

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->total_seats }}

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Available Seats

                            </label>

                            <div class="fw-bold">

                                {{ $vehicle->available_seats }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Vehicle Status --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            Vehicle Status

        </h5>

    </div>

    <div class="card-body text-center">

        @if($vehicle->status === \App\Enums\VehicleStatus::PENDING)

            <span class="badge bg-warning fs-6 px-4 py-2">

                Pending Approval

            </span>

        @elseif($vehicle->status === \App\Enums\VehicleStatus::APPROVED)

            <span class="badge bg-success fs-6 px-4 py-2">

                Approved

            </span>

        @else

            <span class="badge bg-danger fs-6 px-4 py-2">

                Rejected

            </span>

        @endif

    </div>

</div>

{{-- Vehicle Photo --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        Main Vehicle Photo

    </div>

    <div class="card-body text-center">

        @if($vehicle->vehicle_photo)

            <img
                src="{{ asset('storage/'.$vehicle->vehicle_photo) }}"
                class="img-fluid rounded border"
                style="max-height:250px;">

            <div class="mt-3">

                <a
                    href="{{ asset('storage/'.$vehicle->vehicle_photo) }}"
                    target="_blank"
                    class="btn btn-outline-primary btn-sm">

                    View Full Image

                </a>

            </div>

        @else

            <p class="text-muted mb-0">

                No main image uploaded.

            </p>

        @endif

    </div>

</div>

{{-- Vehicle Photo Gallery --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        Vehicle Photo Gallery

    </div>

    <div class="card-body">

        @if(is_array($vehicle->vehicle_photos) && count($vehicle->vehicle_photos))

            <div class="row g-3">

                @foreach($vehicle->vehicle_photos as $photo)

                    <div class="col-6">

                        <div class="card border-0 shadow-sm">

                            <img
                                src="{{ asset('storage/'.$photo) }}"
                                class="card-img-top img-fluid"
                                style="max-height:220px; object-fit:cover;">

                            <div class="card-body p-2 text-center">

                                <a
                                    href="{{ asset('storage/'.$photo) }}"
                                    target="_blank"
                                    class="btn btn-outline-primary btn-sm w-100">

                                    View Photo

                                </a>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <p class="text-muted mb-0">

                No gallery photos uploaded.

            </p>

        @endif

    </div>

</div>

{{-- Documents --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        Documents

    </div>

    <div class="card-body">

        {{-- Registration Book --}}

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <strong>

                    Registration Book

                </strong>

            </div>

            <div>

                @if($vehicle->registration_book)

                    <a
                        href="{{ asset('storage/'.$vehicle->registration_book) }}"
                        target="_blank"
                        class="btn btn-primary btn-sm">

                        View

                    </a>

                @else

                    <span class="text-muted">

                        N/A

                    </span>

                @endif

            </div>

        </div>

        <hr>

        {{-- Fitness Certificate --}}

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <strong>

                    Fitness Certificate

                </strong>

            </div>

            <div>

                @if($vehicle->fitness_certificate)

                    <a
                        href="{{ asset('storage/'.$vehicle->fitness_certificate) }}"
                        target="_blank"
                        class="btn btn-primary btn-sm">

                        View

                    </a>

                @else

                    <span class="text-muted">

                        Not Uploaded

                    </span>

                @endif

            </div>

        </div>

        <hr>

        {{-- Insurance --}}

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <strong>

                    Insurance

                </strong>

            </div>

            <div>

                @if($vehicle->insurance_document)

                    <a
                        href="{{ asset('storage/'.$vehicle->insurance_document) }}"
                        target="_blank"
                        class="btn btn-primary btn-sm">

                        View

                    </a>

                @else

                    <span class="text-muted">

                        Not Uploaded

                    </span>

                @endif

            </div>

        </div>

    </div>

</div>

{{-- Approval Information --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        Review Details

    </div>

    <div class="card-body">

        <div class="mb-3">

            <small class="text-muted">

                Submitted At

            </small>

            <div class="fw-bold">

                {{ $vehicle->created_at->format('d M Y h:i A') }}

            </div>

        </div>

        @if($vehicle->approved_at)

            <div class="mb-3">

                <small class="text-muted">

                    Approved At

                </small>

                <div class="fw-bold text-success">

                    {{ $vehicle->approved_at->format('d M Y h:i A') }}

                </div>

            </div>

        @endif

        @if($vehicle->rejected_at)

            <div class="mb-3">

                <small class="text-muted">

                    Rejected At

                </small>

                <div class="fw-bold text-danger">

                    {{ $vehicle->rejected_at->format('d M Y h:i A') }}

                </div>

            </div>

        @endif

        @if($vehicle->remarks)

            <hr>

            <small class="text-muted">

                Remarks

            </small>

            <div class="alert alert-light mt-2 mb-0">

                {{ $vehicle->remarks }}

            </div>

        @endif

    </div>

</div>

{{-- Review Actions --}}
<div class="card shadow-sm border-0">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            Review

        </h5>

    </div>

    <div class="card-body">

        @if($vehicle->status === \App\Enums\VehicleStatus::PENDING)

            <form
                action="{{ route('admin.vehicles.approve', $vehicle) }}"
                method="POST">

                @csrf

                <button
                    type="submit"
                    class="btn btn-success w-100 mb-3">

                    <i class="bi bi-check-circle"></i>

                    Approve Vehicle

                </button>

            </form>

            <button
                class="btn btn-danger w-100"
                data-bs-toggle="modal"
                data-bs-target="#rejectModal">

                <i class="bi bi-x-circle"></i>

                Reject Vehicle

            </button>

        @elseif($vehicle->status === \App\Enums\VehicleStatus::APPROVED)

            <div class="alert alert-success mb-0">

                <strong>

                    Vehicle Approved

                </strong>

                <hr>

                {{ $vehicle->remarks }}

            </div>

        @else

            <div class="alert alert-danger mb-0">

                <strong>

                    Vehicle Rejected

                </strong>

                <hr>

                {{ $vehicle->remarks }}

            </div>

        @endif

    </div>

</div>

</div>

</div>

</div>

{{-- Reject Modal --}}
<div
    class="modal fade"
    id="rejectModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog">

        <form
            action="{{ route('admin.vehicles.reject',$vehicle) }}"
            method="POST">

            @csrf

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Reject Vehicle

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">

                            Reason

                        </label>

                        <textarea
                            name="remarks"
                            class="form-control @error('remarks') is-invalid @enderror"
                            rows="5"
                            required>{{ old('remarks') }}</textarea>

                        @error('remarks')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Select fields to reject

                        </label>

                        <div class="row">

                            @php
                                $vehicleRejectionFields = [
                                    'vehicle_photo' => 'Vehicle photo',
                                    'registration_book' => 'Registration book',
                                    'fitness_certificate' => 'Fitness certificate',
                                    'insurance_document' => 'Insurance document',
                                    'brand' => 'Brand',
                                    'model' => 'Model',
                                    'manufacture_year' => 'Manufacture year',
                                    'color' => 'Color',
                                    'registration_number' => 'Registration number',
                                    'engine_number' => 'Engine number',
                                    'chassis_number' => 'Chassis number',
                                    'total_seats' => 'Total seats',
                                    'available_seats' => 'Available seats',
                                ];
                            @endphp

                            @foreach($vehicleRejectionFields as $key => $label)

                                <div class="col-6 mb-2">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="rejection_issues[]"
                                            value="{{ $key }}"
                                            id="vehicle_reject_{{ $key }}"
                                            {{ is_array(old('rejection_issues')) && in_array($key, old('rejection_issues')) ? 'checked' : '' }}>

                                        <label
                                            class="form-check-label"
                                            for="vehicle_reject_{{ $key }}">

                                            {{ $label }}

                                        </label>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        @error('rejection_issues')

                            <div class="text-danger small mt-1">

                                {{ $message }}

                            </div>

                        @enderror

                        @error('rejection_issues.*')

                            <div class="text-danger small mt-1">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger">

                        Reject Vehicle

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection
