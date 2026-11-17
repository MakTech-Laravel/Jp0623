<?php

namespace Database\Factories;

use App\Models\LegalPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalPage>
 */
class LegalPageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'title' => fake()->words(3, true),
            'last_updated_on' => fake()->date(),
            'sections' => [
                ['heading' => fake()->sentence(3), 'body' => fake()->paragraph()],
            ],
        ];
    }
}
