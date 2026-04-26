<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'unique_number' => $this->unique_number,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'is_valid' => $this->isValid(),
            'certified_at' => $this->certified_at?->toIso8601String(),
            'verification_url' => $this->verification_url,
            'artwork' => [
                'title' => $this->artwork?->title,
                'type' => $this->artwork?->type?->value,
                'type_label' => $this->artwork?->type?->label(),
                'year' => $this->artwork?->year,
                'image_url' => $this->artwork?->image_path
                    ? Storage::disk('public')->url($this->artwork->image_path)
                    : null,
            ],
            'artist' => [
                'artist_name' => $this->artist?->artistProfile?->artist_name,
            ],
        ];
    }
}
