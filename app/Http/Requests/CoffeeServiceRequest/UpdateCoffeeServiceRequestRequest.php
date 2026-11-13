<?php

namespace App\Http\Requests\CoffeeServiceRequest;

use App\Models\CoffeeServiceRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCoffeeServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(CoffeeServiceRequest::STATUSES),
            ],
            'price' => ['sometimes', 'required', 'integer', 'min:1', 'max:999999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->exists('status') && ! $this->exists('price')) {
                $validator->errors()->add('status', 'Provide a status or price to update.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'Status',
            'price' => 'Price',
        ];
    }
}
