<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TripBooking;

class DriverTrip extends Model
{
    protected $fillable = [

        'driver_id',

        'vehicle_id',

        'route_id',

        'trip_date',

        'departure_time',

        'total_seats',

        'available_seats',

        'status',

        'started_at',

        'completed_at',

        'cancelled_at',

    ];

    protected function casts(): array
    {
        return [

            'trip_date' => 'date',

            'departure_time' => 'datetime:H:i',

            'started_at' => 'datetime',

            'completed_at' => 'datetime',

            'cancelled_at' => 'datetime',

        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function fares(): HasMany
    {
        return $this->hasMany(TripFare::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(DriverTripStop::class, 'driver_trip_id');
    }

    public function bookings(): HasMany
    {
        // TripBooking model may not exist yet in this codebase, return relation by class string to avoid static error
        return $this->hasMany('\App\\Models\\TripBooking');
    }
}
