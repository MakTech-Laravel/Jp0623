<?php

namespace App\Http\Requests\VendingRequest;

use App\Models\VendingRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $employees = $this->input('employees_on_site');
        $visitors = $this->input('daily_visitors');

        $this->merge([
            'employees_on_site' => $employees === '' || $employees === null ? null : $employees,
            'daily_visitors' => $visitors === '' || $visitors === null ? null : $visitors,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_type' => ['required', 'string', Rule::in(VendingRequest::LOCATION_TYPES)],
            'business_name' => ['required', 'string', 'max:255'],
            'address_or_zip' => ['required', 'string', 'max:255'],
            'employees_on_site' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'daily_visitors' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'placement' => ['required', 'string', Rule::in(VendingRequest::PLACEMENTS)],
            'space_notes' => ['nullable', 'string', 'max:2000'],
            'food_on_site' => ['required', 'string', Rule::in(VendingRequest::YES_NO)],
            'comparing_quotes' => ['required', 'string', Rule::in(VendingRequest::YES_NO)],
            'machine_interests' => ['required', 'array', 'min:1'],
            'machine_interests.*' => ['string', Rule::in(VendingRequest::MACHINE_INTERESTS)],
            'machine_count' => ['required', 'string', Rule::in(VendingRequest::MACHINE_COUNTS)],
            'existing_vending' => ['required', 'string', Rule::in(VendingRequest::EXISTING_VENDING)],
            'switch_reason' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(fn (): bool => $this->input('existing_vending') === 'Yes, want to switch'),
            ],
            'contact_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'terms_accepted' => ['accepted'],
            'captcha_confirmed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'location_type' => 'location type',
            'business_name' => 'business name',
            'address_or_zip' => 'address or ZIP',
            'employees_on_site' => 'employees on site',
            'daily_visitors' => 'daily visitors',
            'placement' => 'placement',
            'space_notes' => 'space notes',
            'food_on_site' => 'food options on site',
            'comparing_quotes' => 'comparing quotes',
            'machine_interests' => 'machine interests',
            'machine_count' => 'machine count',
            'existing_vending' => 'existing vending',
            'switch_reason' => 'switch reason',
            'contact_name' => 'name',
            'job_title' => 'job title',
            'email' => 'email',
            'phone' => 'phone',
            'terms_accepted' => 'terms',
            'captcha_confirmed' => 'captcha',
        ];
    }
}
