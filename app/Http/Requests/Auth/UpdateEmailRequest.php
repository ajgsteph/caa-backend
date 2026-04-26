<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',
                'max:191',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'current_password' => ['required', 'current_password'],
        ];
    }
}
