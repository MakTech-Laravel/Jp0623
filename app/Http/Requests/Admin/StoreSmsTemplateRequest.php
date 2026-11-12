<?php

namespace App\Http\Requests\Admin;

use App\Enums\SmsTemplatePurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSmsTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'purpose' => ['required', 'string', Rule::enum(SmsTemplatePurpose::class)],
            'body' => ['required', 'string', 'min:5', 'max:1600'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
