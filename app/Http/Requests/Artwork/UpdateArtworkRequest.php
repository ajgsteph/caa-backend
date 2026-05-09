<?php

namespace App\Http\Requests\Artwork;

use App\Enums\ArtworkType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArtworkRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:191'],
            'type' => ['sometimes', Rule::enum(ArtworkType::class)],
            'technique' => ['nullable', 'string', 'max:191'],
            'dimensions' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'integer', 'min:1500', 'max:'.(int) date('Y')],
            'description' => ['nullable', 'string', 'max:5000'],
            'signature' => ['nullable', 'string', 'max:191'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
