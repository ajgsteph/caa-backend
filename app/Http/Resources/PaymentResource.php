<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'method' => $this->method?->value,
            'method_label' => $this->method?->label(),
            'status' => $this->status?->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'transaction_reference' => $this->transaction_reference,
        ];
    }
}
