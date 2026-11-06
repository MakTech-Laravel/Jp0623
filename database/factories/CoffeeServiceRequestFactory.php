<?php

namespace Database\Factories;

use App\Models\CoffeeServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoffeeServiceRequest>
 */
class CoffeeServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_name' => fake()->company(),
            'zip_code' => (string) fake()->numberBetween(10000, 99999),
            'contact_name' => fake()->name(),
            'phone' => '(555) 555-5555',
            'email' => fake()->safeEmail(),
            'notes' => fake()->optional()->sentence(),
            'terms_accepted' => true,
            'status' => CoffeeServiceRequest::STATUS_NEW,
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CoffeeServiceRequest::STATUS_REVIEWED,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CoffeeServiceRequest::STATUS_CLOSED,
        ]);
    }
}
