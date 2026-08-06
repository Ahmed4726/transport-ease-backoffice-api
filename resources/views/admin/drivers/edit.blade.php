@extends('admin.layouts.app')

@section('title', 'Edit Driver')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Edit Driver</h2>
            <p class="text-muted">Update driver information and status.</p>
        </div>
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Back to drivers</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.drivers.update', $driver) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $driver->user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $driver->user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $driver->user->phone) }}" class="form-control @error('phone') is-invalid @enderror" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="approved" {{ old('status', $driver->user->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ old('status', $driver->user->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ old('status', $driver->user->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNIC</label>
                        <input type="text" name="cnic" value="{{ old('cnic', $driver->cnic) }}" class="form-control @error('cnic') is-invalid @enderror" required>
                        @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" class="form-control @error('license_number') is-invalid @enderror" required>
                        @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Expiry</label>
                        <input type="date" name="license_expiry" value="{{ old('license_expiry', optional($driver->license_expiry)->format('Y-m-d')) }}" class="form-control @error('license_expiry') is-invalid @enderror" required>
                        @error('license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city', $driver->city) }}" class="form-control @error('city') is-invalid @enderror">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" value="{{ old('address', $driver->address) }}" class="form-control @error('address') is-invalid @enderror">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($driver->date_of_birth)->format('Y-m-d')) }}" class="form-control @error('date_of_birth') is-invalid @enderror">
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Blood Group</label>
                        <input type="text" name="blood_group" value="{{ old('blood_group', $driver->blood_group) }}" class="form-control @error('blood_group') is-invalid @enderror">
                        @error('blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}" class="form-control @error('emergency_contact_name') is-invalid @enderror">
                        @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}" class="form-control @error('emergency_contact_phone') is-invalid @enderror">
                        @error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Available</label>
                        <select name="is_available" class="form-select @error('is_available') is-invalid @enderror">
                            <option value="0" {{ old('is_available', $driver->is_available) == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('is_available', $driver->is_available) == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                        @error('is_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                        @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->profile_photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$driver->profile_photo) }}" alt="Profile Photo" class="img-fluid rounded" style="max-height:120px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CNIC Front</label>
                        <input type="file" name="cnic_front" class="form-control @error('cnic_front') is-invalid @enderror" accept="image/*">
                        @error('cnic_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->cnic_front)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$driver->cnic_front) }}" alt="CNIC Front" class="img-fluid rounded" style="max-height:120px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CNIC Back</label>
                        <input type="file" name="cnic_back" class="form-control @error('cnic_back') is-invalid @enderror" accept="image/*">
                        @error('cnic_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->cnic_back)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$driver->cnic_back) }}" alt="CNIC Back" class="img-fluid rounded" style="max-height:120px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">License Front</label>
                        <input type="file" name="license_front" class="form-control @error('license_front') is-invalid @enderror" accept="image/*">
                        @error('license_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->license_front)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$driver->license_front) }}" alt="License Front" class="img-fluid rounded" style="max-height:120px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">License Back</label>
                        <input type="file" name="license_back" class="form-control @error('license_back') is-invalid @enderror" accept="image/*">
                        @error('license_back')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->license_back)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$driver->license_back) }}" alt="License Back" class="img-fluid rounded" style="max-height:120px;">
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
