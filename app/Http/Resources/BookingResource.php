<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDriverManifest = $request->user()?->isDriver() && $this->whenLoaded('passenger');

        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'seats' => $this->seats,
            'from_stop_id' => $this->from_stop_id,
            'to_stop_id' => $this->to_stop_id,
            'status' => $this->status?->value ?? $this->status,
            'fare_per_seat' => $this->fare_per_seat,
            'total_fare' => $this->total_fare,
            'cancelled_at' => $this->cancelled_at,
            'boarded_at' => $this->boarded_at,
            'completed_at' => $this->completed_at,
            'no_show_at' => $this->no_show_at,
            'created_at' => $this->created_at,
            'trip' => $this->whenLoaded('trip', fn () => [
                'id' => $this->trip?->id,
                'status' => $this->trip?->status,
                'trip_date' => $this->trip?->trip_date?->toDateString(),
                'departure_time' => $this->trip?->departure_time?->format('H:i'),
            ]),
            'passenger' => $this->when($isDriverManifest, fn () => [
                'name' => $this->passenger?->user?->name,
            ]),
        ];
    }
}
