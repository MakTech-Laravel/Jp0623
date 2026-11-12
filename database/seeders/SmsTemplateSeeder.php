<?php

namespace Database\Seeders;

use App\Enums\SmsTemplatePurpose;
use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Auto vending lead alert',
                'purpose' => SmsTemplatePurpose::AUTO_VENDING,
                'body' => 'New vending lead near you: {{business_name}}, {{location}}. Contact {{contact_name}} at {{phone}}.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Auto coffee lead alert',
                'purpose' => SmsTemplatePurpose::AUTO_COFFEE,
                'body' => 'New coffee service lead near you: {{business_name}}, {{location}}. Contact {{contact_name}} at {{phone}}.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Manual — new opportunity nearby',
                'purpose' => SmsTemplatePurpose::MANUAL,
                'body' => 'Hi — a new vending opportunity is available near you. Reply or check your operator dashboard for details.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Manual — follow up reminder',
                'purpose' => SmsTemplatePurpose::MANUAL,
                'body' => 'Reminder: a nearby lead is still open. Please review and respond if you can cover the location.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Manual — coffee opportunity',
                'purpose' => SmsTemplatePurpose::MANUAL,
                'body' => 'New office coffee opportunity near your service area. Open your dashboard for contact details.',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::query()->updateOrCreate(
                [
                    'name' => $template['name'],
                    'purpose' => $template['purpose'],
                ],
                $template,
            );
        }
    }
}
