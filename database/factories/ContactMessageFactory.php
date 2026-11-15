<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->numerify('(###) ###-####'),
            'topic' => fake()->randomElement(ContactMessage::TOPICS),
            'message' => fake()->paragraph(),
            'terms_accepted' => true,
            'status' => ContactMessage::STATUS_NEW,
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContactMessage::STATUS_REVIEWED,
        ]);
    }
}
