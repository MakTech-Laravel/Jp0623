<?php

namespace Database\Factories;

use App\Enums\OperatorSmsStatus;
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
            'zip_code' => '77002',
            'address' => '100 Main St',
            'city' => 'Houston',
            'state' => 'TX',
            'postal_code' => '77002',
            'latitude' => 29.7604000,
            'longitude' => -95.3698000,
            'contact_name' => fake()->name(),
            'phone' => '(555) 555-5555',
            'email' => fake()->safeEmail(),
            'notes' => fake()->optional()->sentence(),
            'terms_accepted' => true,
            'status' => CoffeeServiceRequest::STATUS_NEW,
            'operator_sms_status' => OperatorSmsStatus::PENDING->value,
            'operator_sms_sent_count' => 0,
            'operator_sms_failed_count' => 0,
            'operator_sms_provider_status' => null,
            'operator_sms_note' => null,
            'operator_sms_notified_at' => null,
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
