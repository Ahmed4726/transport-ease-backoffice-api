<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class TripBooking extends Model
{
    protected $fillable = [
        'driver_trip_id',
        'passenger_id',
        'from_stop_id',
        'to_stop_id',
        'seats',
        'status',
        'fare_per_seat',
        'total_fare',
        'booking_reference',
        'cancelled_at',
        'cancellation_reason',
        'boarded_at',
        'completed_at',
        'no_show_at',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'status' => BookingStatus::class,
            'fare_per_seat' => 'decimal:2',
            'total_fare' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'boarded_at' => 'datetime',
            'completed_at' => 'datetime',
            'no_show_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (TripBooking $booking): void {
            if (!$booking->isDirty('status')) {
                return;
            }

            $from = $booking->getRawOriginal('status');
            $to = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;
            $allowed = match ($from) {
                BookingStatus::CONFIRMED->value => [BookingStatus::CONFIRMED->value, BookingStatus::CANCELLED->value, BookingStatus::BOARDED->value, BookingStatus::NO_SHOW->value],
                BookingStatus::BOARDED->value => [BookingStatus::BOARDED->value, BookingStatus::COMPLETED->value],
                default => [$from],
            };

            if (!in_array($to, $allowed, true)) {
                throw new InvalidArgumentException("Invalid booking status transition from {$from} to {$to}.");
            }
        });
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(DriverTrip::class, 'driver_trip_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(CityStop::class, 'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(CityStop::class, 'to_stop_id');
    }
}
