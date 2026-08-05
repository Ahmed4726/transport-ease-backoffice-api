<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRejectionHistory extends Model
{
    protected $fillable = [
        'vehicle_id',
        'admin_id',
        'remarks',
        'rejection_issues',
    ];

    protected function casts(): array
    {
        return [
            'rejection_issues' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
