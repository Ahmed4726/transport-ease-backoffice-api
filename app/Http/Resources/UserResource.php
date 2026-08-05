<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'role' => $this->role,

            'status' => $this->status,

            'profile_photo' => $this->profile_photo,

            'driver' => $this->whenLoaded(
                'driver',
                fn () => new DriverResource($this->driver),
            ),

            'created_at' => optional($this->created_at)
                ->toDateTimeString(),

        ];
    }
}
