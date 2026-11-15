<?php

namespace App\Http\Requests\ContactMessage;

use App\Models\ContactMessage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $company = $this->input('company');
        $phone = $this->input('phone');

        $this->merge([
            'company' => $company === '' || $company === null ? null : $company,
            'phone' => $phone === '' || $phone === null ? null : $phone,
            'terms_accepted' => filter_var(
                $this->input('terms_accepted'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ) ?? false,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9+\-() ]+$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                    $length = strlen($digits);

                    if ($length < 7 || $length > 15) {
                        $fail('The phone must contain between 7 and 15 digits.');
                    }
                },
            ],
            'topic' => ['required', 'string', Rule::in(ContactMessage::TOPICS)],
            'message' => ['required', 'string', 'max:5000'],
            'terms_accepted' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'company' => 'company',
            'email' => 'email',
            'phone' => 'phone',
            'topic' => 'topic',
            'message' => 'message',
            'terms_accepted' => 'terms',
        ];
    }
}
