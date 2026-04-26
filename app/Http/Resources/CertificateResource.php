<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unique_number' => $this->unique_number,
            'status' => $this->status?->value,
            'certified_at' => $this->certified_at?->toIso8601String(),
            'verification_url' => $this->verification_url,
            'qr_code_url' => $this->qr_code_path ? Storage::disk('public')->url($this->qr_code_path) : null,
            'has_pdf' => (bool) $this->pdf_path,
            'revocation_reason' => $this->revocation_reason,
            'artwork' => ArtworkResource::make($this->whenLoaded('artwork')),
            'client' => ClientResource::make($this->whenLoaded('client')),
            'artist' => ArtistResource::make($this->whenLoaded('artist')),
            'payment' => PaymentResource::make($this->whenLoaded('payment')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
