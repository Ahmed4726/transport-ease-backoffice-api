@extends('admin.layouts.app')

@section('title', 'Create Vehicle')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Create Vehicle</h2>
            <p class="text-muted">Add a new vehicle and approve it automatically.</p>
        </div>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Back to vehicles</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} ({{ $driver->user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type_id" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}" {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}" class="form-control @error('brand') is-invalid @enderror" required>
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" value="{{ old('model') }}" class="form-control @error('model') is-invalid @enderror" required>
                        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Manufacture Year</label>
                        <input type="number" name="manufacture_year" value="{{ old('manufacture_year') }}" class="form-control @error('manufacture_year') is-invalid @enderror" min="1900" max="{{ now()->year }}" required>
                        @error('manufacture_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" value="{{ old('color') }}" class="form-control @error('color') is-invalid @enderror" required>
                        @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number') }}" class="form-control @error('registration_number') is-invalid @enderror" required>
                        @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Engine Number</label>
                        <input type="text" name="engine_number" value="{{ old('engine_number') }}" class="form-control @error('engine_number') is-invalid @enderror">
                        @error('engine_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chassis Number</label>
                        <input type="text" name="chassis_number" value="{{ old('chassis_number') }}" class="form-control @error('chassis_number') is-invalid @enderror">
                        @error('chassis_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Seats</label>
                        <input type="number" name="total_seats" value="{{ old('total_seats') }}" class="form-control @error('total_seats') is-invalid @enderror" min="1" required>
                        @error('total_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="available_seats" value="{{ old('available_seats') }}" class="form-control @error('available_seats') is-invalid @enderror" min="1" required>
                        @error('available_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Vehicle Photo</label>
                        <input type="file" name="vehicle_photo" class="form-control @error('vehicle_photo') is-invalid @enderror" accept="image/*" required>
                        @error('vehicle_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Registration Book</label>
                        <input type="file" name="registration_book" class="form-control @error('registration_book') is-invalid @enderror" accept="image/*" required>
                        @error('registration_book')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fitness Certificate</label>
                        <input type="file" name="fitness_certificate" class="form-control @error('fitness_certificate') is-invalid @enderror" accept="image/*">
                        @error('fitness_certificate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Insurance Document</label>
                        <input type="file" name="insurance_document" class="form-control @error('insurance_document') is-invalid @enderror" accept="image/*">
                        @error('insurance_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Additional Vehicle Photos</label>
                        <input type="file" name="vehicle_photos[]" class="form-control @error('vehicle_photos') is-invalid @enderror" accept="image/*" multiple>
                        @error('vehicle_photos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('vehicle_photos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
