<?php

namespace Database\Factories;

use App\Models\OperatorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperatorProfile>
 */
class OperatorProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company_name' => fake()->company(),
            'phone' => null,
            'owns_machines' => true,
            'machine_types' => ['Combo', 'Snack'],
            'machines_needed' => '1',
            'primary_city' => fake()->city().', TX',
            'terms_accepted_at' => now(),
            'email_code_confirmed_at' => now(),
            'trial_started_at' => now(),
        ];
    }
}
