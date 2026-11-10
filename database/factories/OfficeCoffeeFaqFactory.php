<?php

namespace Database\Factories;

use App\Models\OfficeCoffeeFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeCoffeeFaq>
 */
class OfficeCoffeeFaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->unique()->sentence().'?',
            'answer' => fake()->paragraph(),
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
