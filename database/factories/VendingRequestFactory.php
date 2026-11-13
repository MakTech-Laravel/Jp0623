<?php

namespace Database\Factories;

use App\Enums\OperatorSmsStatus;
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
            'location_type' => fake()->randomElement([
                'Business / office',
                'Warehouse / factory',
                'Apartment / hotel',
                'School / college',
                'Medical',
                'Gym',
                'Other',
            ]),
            'business_name' => fake()->company(),
            'address_or_zip' => 'Houston, TX 77002',
            'address' => '100 Main St',
            'city' => 'Houston',
            'state' => 'TX',
            'postal_code' => '77002',
            'latitude' => 29.7604000,
            'longitude' => -95.3698000,
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
            'price' => (int) config('leads.price', 150),
            'status' => VendingRequest::STATUS_NEW,
            'operator_sms_status' => OperatorSmsStatus::PENDING->value,
            'operator_sms_sent_count' => 0,
            'operator_sms_failed_count' => 0,
            'operator_sms_provider_status' => null,
            'operator_sms_note' => null,
            'operator_sms_notified_at' => null,
        ];
    }

    public function unpriced(): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => null,
        ]);
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
