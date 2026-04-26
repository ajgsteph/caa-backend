<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s\-]{6,30}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Le format du numéro de téléphone est invalide.',
        ];
    }
}
