@extends('admin.layouts.app')

@section('title', 'Create Driver')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Create Driver</h2>
            <p class="text-muted">Add a new driver and auto-approve the record.</p>
        </div>
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Back to drivers</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.drivers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNIC</label>
                        <input type="text" name="cnic" value="{{ old('cnic') }}" class="form-control @error('cnic') is-invalid @enderror" required>
                        @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" class="form-control @error('license_number') is-invalid @enderror" required>
                        @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Expiry</label>
                        <input type="date" name="license_expiry" value="{{ old('license_expiry') }}" class="form-control @error('license_expiry') is-invalid @enderror" required>
                        @error('license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="form-control @error('city') is-invalid @enderror">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control @error('date_of_birth') is-invalid @enderror">
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Blood Group</label>
                        <input type="text" name="blood_group" value="{{ old('blood_group') }}" class="form-control @error('blood_group') is-invalid @enderror">
                        @error('blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="form-control @error('emergency_contact_name') is-invalid @enderror">
                        @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="form-control @error('emergency_contact_phone') is-invalid @enderror">
                        @error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Available</label>
                        <select name="is_available" class="form-select @error('is_available') is-invalid @enderror">
                            <option value="0" {{ old('is_available')=='0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('is_available')=='1' ? 'selected' : '' }}>Yes</option>
                        </select>
                        @error('is_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*" required>
                        @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CNIC Front</label>
                        <input type="file" name="cnic_front" class="form-control @error('cnic_front') is-invalid @enderror" accept="image/*" required>
                        @error('cnic_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CNIC Back</label>
                        <input type="file" name="cnic_back" class="form-control @error('cnic_back') is-invalid @enderror" accept="image/*" required>
                        @error('cnic_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">License Front</label>
                        <input type="file" name="license_front" class="form-control @error('license_front') is-invalid @enderror" accept="image/*" required>
                        @error('license_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">License Back</label>
                        <input type="file" name="license_back" class="form-control @error('license_back') is-invalid @enderror" accept="image/*" required>
                        @error('license_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
