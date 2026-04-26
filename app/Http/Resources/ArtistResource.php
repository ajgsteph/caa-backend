<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'artist_name' => $this->artistProfile?->artist_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'registered_at' => $this->registered_at?->toIso8601String(),
            'roles' => $this->getRoleNames(),
        ];
    }
}
