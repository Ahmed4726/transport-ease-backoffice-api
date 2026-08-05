<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    protected $fillable = [

        'route_id',

        'city_id',

        'location_name',

        'address',

        'latitude',

        'longitude',

        'google_place_id',

        'stop_order',

        'distance_from_start',

        'estimated_minutes',

    ];

    protected function casts(): array
    {
        return [

            'distance_from_start'=>'float',

            'estimated_minutes'=>'integer',

            'latitude' => 'float',

            'longitude' => 'float',

        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function fromFares()
    {
        return $this->hasMany(RouteFare::class,'from_stop_id');
    }

    public function toFares()
    {
        return $this->hasMany(RouteFare::class,'to_stop_id');
    }

    public function departureFares()
    {
        return $this->hasMany(TripFare::class,'from_stop_id');
    }

    public function destinationFares()
    {
        return $this->hasMany(TripFare::class,'to_stop_id');
    }
}
