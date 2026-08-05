<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{

    protected $fillable = [
        'user_id',
        'cnic',
        'license_number',
        'license_expiry',
        'profile_photo',
        'cnic_front',
        'cnic_back',
        'license_front',
        'license_back',
        'remarks',
        'address',
        'city',
        'date_of_birth',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_group',
        'is_available',
        'rating',
        'completed_trips',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_issues',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'is_available' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'rejection_issues' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class)
            ->latestOfMany();
    }

    public function pendingVehicle()
    {
        return $this->hasOne(Vehicle::class)
            ->where('status', \App\Enums\VehicleStatus::PENDING);
    }

}
