<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [

        'name',

        'province',

        'latitude',

        'longitude',

        'is_active',

    ];

    protected function casts(): array
    {
        return [

            'latitude' => 'float',

            'longitude' => 'float',

            'is_active' => 'boolean',

        ];
    }

    public function routeStarts(): HasMany
    {
        return $this->hasMany(Route::class, 'start_city_id');
    }

    public function routeEnds(): HasMany
    {
        return $this->hasMany(Route::class, 'end_city_id');
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function cityStops(): HasMany
    {
        return $this->hasMany(\App\Models\CityStop::class);
    }

}
