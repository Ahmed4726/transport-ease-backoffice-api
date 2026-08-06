@extends('admin.layouts.app')

@section('title', 'Edit Vehicle')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Edit Vehicle</h2>
            <p class="text-muted">Update vehicle details.</p>
        </div>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Back to vehicles</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $vehicle->driver_id) == $driver->id ? 'selected' : '' }}>
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
                                <option value="{{ $type->id }}" {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}" class="form-control @error('brand') is-invalid @enderror" required>
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" class="form-control @error('model') is-invalid @enderror" required>
                        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Manufacture Year</label>
                        <input type="number" name="manufacture_year" value="{{ old('manufacture_year', $vehicle->manufacture_year) }}" class="form-control @error('manufacture_year') is-invalid @enderror" min="1900" max="{{ now()->year }}" required>
                        @error('manufacture_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" class="form-control @error('color') is-invalid @enderror" required>
                        @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" class="form-control @error('registration_number') is-invalid @enderror" required>
                        @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Engine Number</label>
                        <input type="text" name="engine_number" value="{{ old('engine_number', $vehicle->engine_number) }}" class="form-control @error('engine_number') is-invalid @enderror">
                        @error('engine_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chassis Number</label>
                        <input type="text" name="chassis_number" value="{{ old('chassis_number', $vehicle->chassis_number) }}" class="form-control @error('chassis_number') is-invalid @enderror">
                        @error('chassis_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Seats</label>
                        <input type="number" name="total_seats" value="{{ old('total_seats', $vehicle->total_seats) }}" class="form-control @error('total_seats') is-invalid @enderror" min="1" required>
                        @error('total_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="available_seats" value="{{ old('available_seats', $vehicle->available_seats) }}" class="form-control @error('available_seats') is-invalid @enderror" min="1" required>
                        @error('available_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Vehicle Photo</label>
                        <input type="file" name="vehicle_photo" class="form-control @error('vehicle_photo') is-invalid @enderror" accept="image/*">
                        @error('vehicle_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($vehicle->vehicle_photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$vehicle->vehicle_photo) }}" class="img-fluid rounded" style="max-height:120px;" alt="Vehicle Photo">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Registration Book</label>
                        <input type="file" name="registration_book" class="form-control @error('registration_book') is-invalid @enderror" accept="image/*">
                        @error('registration_book')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($vehicle->registration_book)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$vehicle->registration_book) }}" class="img-fluid rounded" style="max-height:120px;" alt="Registration Book">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fitness Certificate</label>
                        <input type="file" name="fitness_certificate" class="form-control @error('fitness_certificate') is-invalid @enderror" accept="image/*">
                        @error('fitness_certificate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($vehicle->fitness_certificate)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$vehicle->fitness_certificate) }}" class="img-fluid rounded" style="max-height:120px;" alt="Fitness Certificate">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Insurance Document</label>
                        <input type="file" name="insurance_document" class="form-control @error('insurance_document') is-invalid @enderror" accept="image/*">
                        @error('insurance_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($vehicle->insurance_document)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$vehicle->insurance_document) }}" class="img-fluid rounded" style="max-height:120px;" alt="Insurance Document">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Additional Vehicle Photos</label>
                        <input type="file" name="vehicle_photos[]" class="form-control @error('vehicle_photos') is-invalid @enderror" accept="image/*" multiple>
                        @error('vehicle_photos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('vehicle_photos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if(is_array($vehicle->vehicle_photos) && count($vehicle->vehicle_photos))
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                @foreach($vehicle->vehicle_photos as $photo)
                                    <img src="{{ asset('storage/'.$photo) }}" class="img-fluid rounded" style="max-height:100px;" alt="Vehicle Photo">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
