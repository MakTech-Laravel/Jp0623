<?php

namespace App\Http\Requests\Article;

use App\Models\Article;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
        /** @var Article $article */
        $article = $this->route('article');

        return [
            'category' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('articles', 'slug')->ignore($article),
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:50000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category' => 'Category',
            'title' => 'Title',
            'slug' => 'Slug',
            'excerpt' => 'Excerpt',
            'body' => 'Body',
            'image' => 'Image',
            'sort_order' => 'Sort order',
            'is_active' => 'Active',
        ];
    }
}
