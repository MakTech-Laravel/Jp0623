<?php

namespace Database\Factories;

use App\Models\CoffeeMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoffeeMachine>
 */
class CoffeeMachineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'text' => fake()->sentence(),
            'tag' => fake()->numberBetween(5, 50).' people',
            'image' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
