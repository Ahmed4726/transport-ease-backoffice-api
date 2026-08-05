<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripFare extends Model
{
    protected $fillable = [

        'driver_trip_id',

        'from_stop_id',

        'to_stop_id',

        'fare'

    ];

    protected function casts(): array
    {
        return [

            'fare' => 'float'

        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(DriverTrip::class,'driver_trip_id');
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(CityStop::class,'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(CityStop::class,'to_stop_id');
    }
}
