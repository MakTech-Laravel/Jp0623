<?php

namespace Database\Factories;

use App\Models\VendingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendingRequest>
 */
class VendingRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_type' => fake()->randomElement(VendingRequest::LOCATION_TYPES),
            'business_name' => fake()->company(),
            'address_or_zip' => (string) fake()->numberBetween(10000, 99999),
            'employees_on_site' => fake()->numberBetween(10, 200),
            'daily_visitors' => fake()->numberBetween(20, 500),
            'placement' => fake()->randomElement(VendingRequest::PLACEMENTS),
            'space_notes' => fake()->optional()->sentence(),
            'food_on_site' => fake()->randomElement(VendingRequest::YES_NO),
            'comparing_quotes' => fake()->randomElement(VendingRequest::YES_NO),
            'machine_interests' => fake()->randomElements(VendingRequest::MACHINE_INTERESTS, 2),
            'machine_count' => fake()->randomElement(VendingRequest::MACHINE_COUNTS),
            'existing_vending' => 'No',
            'switch_reason' => null,
            'contact_name' => fake()->name(),
            'job_title' => fake()->optional()->jobTitle(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'terms_accepted' => true,
            'status' => VendingRequest::STATUS_NEW,
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VendingRequest::STATUS_REVIEWED,
        ]);
    }

    public function switching(): static
    {
        return $this->state(fn (array $attributes): array => [
            'existing_vending' => 'Yes, want to switch',
            'switch_reason' => 'Looking for better service.',
        ]);
    }
}
