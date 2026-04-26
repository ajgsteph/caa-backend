<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArtworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'technique' => $this->technique,
            'dimensions' => $this->dimensions,
            'year' => $this->year,
            'description' => $this->description,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'signature' => $this->signature,
        ];
    }
}
