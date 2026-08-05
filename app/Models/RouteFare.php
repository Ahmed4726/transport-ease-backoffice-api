<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteFare extends Model
{
    protected $fillable = [

        'route_id',

        'from_stop_id',

        'to_stop_id',

        'fare'

    ];

    protected function casts(): array
    {
        return [

            'fare'=>'float'

        ];
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function fromStop()
    {
        return $this->belongsTo(RouteStop::class,'from_stop_id');
    }

    public function toStop()
    {
        return $this->belongsTo(RouteStop::class,'to_stop_id');
    }
}
