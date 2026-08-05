<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $fillable = [

        'name',

        'start_city_id',

        'end_city_id',

        'distance',

        'estimated_duration',

        'is_active',

    ];

    protected function casts(): array
    {
        return [

            'distance'=>'float',

            'estimated_duration'=>'integer',

            'is_active'=>'boolean',

        ];
    }

    public function startCity(): BelongsTo
    {
        return $this->belongsTo(City::class,'start_city_id');
    }

    public function endCity(): BelongsTo
    {
        return $this->belongsTo(City::class,'end_city_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(DriverTrip::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class)
            ->orderBy('stop_order');
    }

    public function fares()
    {
        return $this->hasMany(RouteFare::class);
    }
}
