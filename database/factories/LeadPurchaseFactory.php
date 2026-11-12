<?php

namespace Database\Factories;

use App\Models\LeadPurchase;
use App\Models\User;
use App\Models\VendingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadPurchase>
 */
class LeadPurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'leadable_type' => 'vending',
            'leadable_id' => VendingRequest::factory(),
            'price' => (int) config('leads.price'),
            'purchased_at' => now(),
        ];
    }
}
