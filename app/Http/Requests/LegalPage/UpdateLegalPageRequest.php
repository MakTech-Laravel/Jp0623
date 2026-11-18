<?php

namespace App\Http\Requests\LegalPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'last_updated_on' => ['nullable', 'date'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.heading' => ['nullable', 'string', 'max:255'],
            'sections.*.body' => ['required', 'string'],
        ];
    }
}
