<?php

namespace Database\Factories;

use App\Models\LeadPurchase;
use App\Models\User;
use App\Models\VendingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LeadPurchase>
 */
class LeadPurchaseFactory extends Factory
{
    /**
     * Define the model's default state. Defaults to a paid Square purchase of a
     * vending lead; the lead lock is derived from the lead after creation.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = (int) config('leads.price');

        return [
            'user_id' => User::factory(),
            'leadable_type' => 'vending',
            'leadable_id' => VendingRequest::factory(),
            'price' => $price,
            'status' => LeadPurchase::STATUS_PAID,
            'amount_cents' => $price * 100,
            'currency' => (string) config('services.square.currency', 'USD'),
            'payment_provider' => LeadPurchase::PROVIDER_SQUARE,
            'idempotency_key' => (string) Str::uuid(),
            'square_payment_id' => 'sqpay_'.Str::random(20),
            'square_status' => 'COMPLETED',
            'square_receipt_url' => 'https://squareup.com/receipt/preview/'.Str::random(20),
            'card_brand' => 'VISA',
            'card_last4' => '1111',
            'paid_at' => now(),
            'purchased_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (LeadPurchase $purchase): void {
            if ($purchase->lead_lock === null
                && in_array($purchase->status, [LeadPurchase::STATUS_PAID, LeadPurchase::STATUS_PENDING], true)
                && is_int($purchase->leadable_id)) {
                $purchase->lead_lock = LeadPurchase::leadKey($purchase->leadable_type, $purchase->leadable_id);
            }
        });
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadPurchase::STATUS_PENDING,
            'square_payment_id' => null,
            'square_status' => null,
            'square_receipt_url' => null,
            'card_brand' => null,
            'card_last4' => null,
            'paid_at' => null,
            'purchased_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadPurchase::STATUS_FAILED,
            'lead_lock' => null,
            'square_payment_id' => null,
            'square_status' => 'FAILED',
            'square_receipt_url' => null,
            'card_brand' => null,
            'card_last4' => null,
            'failure_code' => 'GENERIC_DECLINE',
            'failure_category' => 'PAYMENT_METHOD_ERROR',
            'paid_at' => null,
            'purchased_at' => null,
        ]);
    }

    public function legacy(): static
    {
        return $this->state(fn (): array => [
            'payment_provider' => LeadPurchase::PROVIDER_LEGACY,
            'idempotency_key' => null,
            'square_payment_id' => null,
            'square_status' => null,
            'square_receipt_url' => null,
            'card_brand' => null,
            'card_last4' => null,
        ]);
    }
}
