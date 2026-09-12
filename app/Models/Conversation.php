<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'booking_id',
        'driver_trip_id',
        'driver_id',
        'passenger_id',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TripBooking::class, 'booking_id');
    }

    public function driverTrip(): BelongsTo
    {
        return $this->belongsTo(DriverTrip::class, 'driver_trip_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
}
