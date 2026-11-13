<?php

namespace Database\Factories;

use App\Models\OperatorMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperatorMembership>
 */
class OperatorMembershipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OperatorMembership::STATUS_ACTIVE_FREE,
            'monthly_price' => (int) config('membership.default_monthly_price', 29),
            'square_customer_id' => null,
            'square_card_id' => null,
            'card_brand' => null,
            'card_last4' => null,
            'current_period_start' => null,
            'current_period_end' => null,
            'next_billing_at' => null,
            'canceled_at' => null,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (): array => [
            'status' => OperatorMembership::STATUS_REQUIRED,
        ]);
    }

    public function active(): static
    {
        $start = now()->subDays(5);
        $end = $start->copy()->addDays(30);

        return $this->state(fn (): array => [
            'status' => OperatorMembership::STATUS_ACTIVE,
            'square_customer_id' => 'CUST-'.fake()->uuid(),
            'square_card_id' => 'CARD-'.fake()->uuid(),
            'card_brand' => 'VISA',
            'card_last4' => '1111',
            'current_period_start' => $start,
            'current_period_end' => $end,
            'next_billing_at' => $end,
        ]);
    }

    public function pastDue(): static
    {
        return $this->active()->state(fn (): array => [
            'status' => OperatorMembership::STATUS_PAST_DUE,
            'next_billing_at' => now()->subDay(),
        ]);
    }
}
