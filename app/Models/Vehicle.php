<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [

        'driver_id',

        'vehicle_type_id',

        'brand',

        'model',

        'manufacture_year',

        'color',

        'registration_number',

        'engine_number',

        'chassis_number',

        'total_seats',

        'available_seats',

        'vehicle_photo',

        'vehicle_photos',

        'registration_book',

        'fitness_certificate',

        'insurance_document',

        'status',

        'approved_by',

        'approved_at',

        'rejected_by',

        'rejected_at',

        'remarks',

        'rejection_issues',

    ];

    protected $casts = [

        'status' => VehicleStatus::class,

        'approved_at' => 'datetime',

        'rejected_at' => 'datetime',

        'rejection_issues' => 'array',

        'vehicle_photos' => 'array',

    ];

    public function driver(): BelongsTo
    { 
        return $this->belongsTo(Driver::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function rejectionHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VehicleRejectionHistory::class);
    }
}

