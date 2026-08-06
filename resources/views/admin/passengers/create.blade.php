@extends('admin.layouts.app')

@section('title', 'Create Passenger')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Create Passenger</h2>
            <p class="text-muted">Add a new passenger record.</p>
        </div>
        <a href="{{ route('admin.passengers.index') }}" class="btn btn-secondary">Back to passengers</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.passengers.store') }}">
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
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="approved" {{ old('status')=='approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ old('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ old('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password Confirmation</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Passenger</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
