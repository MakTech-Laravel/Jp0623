<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendOperatorSmsRequest extends FormRequest
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
            'operator_ids' => ['required', 'array', 'min:1'],
            'operator_ids.*' => ['integer', 'distinct', 'exists:operator_profiles,id'],
            'message' => ['required', 'string', 'min:1', 'max:1600'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'operator_ids.required' => 'Select at least one operator.',
            'operator_ids.min' => 'Select at least one operator.',
            'message.required' => 'Message is required.',
        ];
    }
}
