<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'slug' => str($title)->slug()->toString().'-'.fake()->unique()->numberBetween(1, 99999),
            'category' => fake()->randomElement(['Vending management', 'Healthy vending', 'Smart & AI vending']),
            'title' => $title,
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraph()."\n\n".fake()->paragraph(),
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
