<?php

namespace App\Http\Requests\CoffeeType;

use App\Models\CoffeeType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoffeeTypeRequest extends FormRequest
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
        /** @var CoffeeType $coffeeType */
        $coffeeType = $this->route('coffee_type');

        return [
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('coffee_types', 'label')->ignore($coffeeType->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'label' => 'Label',
            'sort_order' => 'Sort order',
            'is_active' => 'Active',
        ];
    }
}
