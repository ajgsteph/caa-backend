<?php

namespace App\Http\Requests\Certificate;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Artwork;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole(UserRole::ARTIST->value)
            && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            // Œuvre existante appartenant à l'artiste
            'artwork_id' => [
                'required',
                'integer',
                Rule::exists('artworks', 'id')->where('artist_id', $this->user()?->id),
            ],

            // Client
            'client.last_name' => ['required', 'string', 'max:120'],
            'client.first_name' => ['required', 'string', 'max:120'],
            'client.email' => ['required', 'email:rfc', 'max:191'],
            'client.phone' => ['nullable', 'string', 'max:30'],

            // Payment
            'payment.method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $artworkId = $this->integer('artwork_id');
            if ($artworkId && Artwork::where('id', $artworkId)->whereHas('certificate')->exists()) {
                $v->errors()->add('artwork_id', 'Cette œuvre possède déjà un certificat.');
            }
        });
    }
}
