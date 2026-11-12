<?php

namespace Database\Factories;

use App\Models\PaymentEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentEvent>
 */
class PaymentEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'square',
            'event_id' => (string) Str::uuid(),
            'type' => 'payment.updated',
            'payload' => [],
        ];
    }
}
