<?php

namespace Database\Factories;

use App\Models\MembershipPayment;
use App\Models\OperatorMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MembershipPayment>
 */
class MembershipPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = (int) config('membership.default_monthly_price', 29);

        return [
            'user_id' => User::factory(),
            'operator_membership_id' => OperatorMembership::factory(),
            'price' => $price,
            'amount_cents' => $price * 100,
            'currency' => 'USD',
            'status' => MembershipPayment::STATUS_PAID,
            'payment_provider' => MembershipPayment::PROVIDER_SQUARE,
            'idempotency_key' => (string) Str::uuid(),
            'square_payment_id' => 'PAY-'.Str::upper(Str::random(10)),
            'square_status' => 'COMPLETED',
            'square_receipt_url' => 'https://squareup.com/receipt/preview/demo',
            'card_brand' => 'VISA',
            'card_last4' => '1111',
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => MembershipPayment::STATUS_PENDING,
            'square_payment_id' => null,
            'square_status' => null,
            'square_receipt_url' => null,
            'paid_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => MembershipPayment::STATUS_FAILED,
            'paid_at' => null,
            'failure_code' => 'CARD_DECLINED',
            'failure_message' => 'Card declined',
        ]);
    }
}
