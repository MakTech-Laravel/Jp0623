<?php

namespace Database\Factories;

use App\Enums\SmsTemplatePurpose;
use App\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsTemplate>
 */
class SmsTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'purpose' => SmsTemplatePurpose::MANUAL,
            'body' => 'New lead near you: {{business_name}} at {{location}}. Call {{contact_name}} at {{phone}}.',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function autoVending(): static
    {
        return $this->state(fn (): array => [
            'purpose' => SmsTemplatePurpose::AUTO_VENDING,
            'name' => 'Auto vending',
        ]);
    }

    public function autoCoffee(): static
    {
        return $this->state(fn (): array => [
            'purpose' => SmsTemplatePurpose::AUTO_COFFEE,
            'name' => 'Auto coffee',
        ]);
    }
}
