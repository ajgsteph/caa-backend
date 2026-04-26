<?php

namespace App\Http\Requests\Certificate;

use App\Models\Certificate;
use Illuminate\Foundation\Http\FormRequest;

class RevokeCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $certificate = $this->route('certificate');

        return $certificate instanceof Certificate
            && $this->user()?->can('revoke', $certificate);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.min' => 'Le motif doit contenir au moins 10 caractères.',
        ];
    }
}
