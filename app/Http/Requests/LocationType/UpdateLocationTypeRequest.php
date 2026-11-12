<?php

namespace App\Http\Requests\LocationType;

use App\Models\LocationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationTypeRequest extends FormRequest
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
        /** @var LocationType $locationType */
        $locationType = $this->route('location_type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('location_types', 'name')->ignore($locationType->id),
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
            'name' => 'Name',
            'sort_order' => 'Sort order',
            'is_active' => 'Active',
        ];
    }
}
