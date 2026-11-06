<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->hasFile('image') && $this->input('image') === '') {
            $this->merge(['image' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'kind' => ['required', 'string', 'max:100'],
            'tags' => ['nullable'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'slug' => 'Slug',
            'kind' => 'Kind',
            'tags' => 'Tags',
            'description' => 'Description',
            'image' => 'Image',
            'is_featured' => 'Featured',
            'is_active' => 'Active',
            'sort_order' => 'Sort order',
        ];
    }
}
