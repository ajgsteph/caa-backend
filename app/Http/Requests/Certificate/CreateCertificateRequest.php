<?php

namespace App\Http\Requests\Certificate;

use App\Enums\ArtworkType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
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
            // Artwork
            'artwork.title' => ['required', 'string', 'max:191'],
            'artwork.type' => ['required', Rule::enum(ArtworkType::class)],
            'artwork.technique' => ['nullable', 'string', 'max:191'],
            'artwork.dimensions' => ['nullable', 'string', 'max:120'],
            'artwork.year' => ['nullable', 'integer', 'min:1500', 'max:'.(int) date('Y')],
            'artwork.description' => ['nullable', 'string', 'max:5000'],
            'artwork.signature' => ['nullable', 'string', 'max:191'],
            'artwork.image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

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
            if (! $this->hasFile('artwork.image')) {
                $v->errors()->add('artwork.image', 'L\'image de l\'œuvre est requise.');
            }
        });
    }
}
