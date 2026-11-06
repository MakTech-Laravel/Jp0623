<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $slug = Str::slug($name).'-'.fake()->unique()->numerify('###');

        return [
            'name' => Str::title($name),
            'slug' => $slug,
            'kind' => Str::slug(fake()->word()),
            'tags' => [fake()->word(), fake()->word()],
            'description' => fake()->sentence(12),
            'image' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
