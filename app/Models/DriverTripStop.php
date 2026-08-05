<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTripStop extends Model
{
    protected $table = 'driver_trip_stops';

    protected $fillable = [
        'driver_trip_id',
        'route_stop_id',
        'stop_order',
    ];

    public function trip()
    {
        return $this->belongsTo(DriverTrip::class, 'driver_trip_id');
    }

    public function stop()
    {
        return $this->belongsTo(CityStop::class, 'route_stop_id');
    }
}
