<?php

namespace App\Enums;

enum BookingStatus: string
{
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case BOARDED = 'boarded';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';

    public function consumesSeat(): bool
    {
        return in_array($this, [self::CONFIRMED, self::BOARDED], true);
    }
}
