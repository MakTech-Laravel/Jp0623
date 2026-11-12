<?php

namespace App\Http\Requests\Operator;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The only client input is the single-use card token from Square's Web
     * Payments SDK; the price is always decided on the server.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_id' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'source_id' => 'card',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source_id.required' => 'Enter your card details to complete the purchase.',
        ];
    }
}
