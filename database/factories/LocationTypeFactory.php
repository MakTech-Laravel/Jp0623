<?php

namespace Database\Factories;

use App\Models\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LocationType>
 */
class LocationTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'image' => null,
            'kind' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'show_on_industries' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function hiddenFromIndustries(): static
    {
        return $this->state(fn (array $attributes): array => [
            'show_on_industries' => false,
        ]);
    }
}
