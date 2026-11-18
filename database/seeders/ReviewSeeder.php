<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Seed the current home-page customer reviews.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $item) {
            Review::query()->updateOrCreate(
                ['quote' => $item['quote']],
                [
                    'role' => $item['role'],
                    'org' => $item['org'],
                    'rating' => 5,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return list<array{quote: string, role: string, org: string}>
     */
    private function items(): array
    {
        return [
            [
                'quote' => 'We have four warehouses and now one person to contact for all of them. Machines were in within two weeks.',
                'role' => 'Facilities Manager',
                'org' => 'Logistics company, TX',
            ],
            [
                'quote' => 'The Vendera cooler was a hit with our night shift. Restocking has been on time every week.',
                'role' => 'Plant Supervisor',
                'org' => 'Manufacturer, OH',
            ],
            [
                'quote' => 'Residents love the market in our lobby, and it didn\'t cost the property anything.',
                'role' => 'Property Manager',
                'org' => 'Apartment community, FL',
            ],
            [
                'quote' => 'Setup was simple and our staff actually uses the snack machine every day. Service has been reliable.',
                'role' => 'Office Manager',
                'org' => 'Accounting firm, GA',
            ],
            [
                'quote' => 'We needed healthy options for a hospital break room. They matched the mix and keep it stocked.',
                'role' => 'Dietary Services Lead',
                'org' => 'Regional hospital, NC',
            ],
            [
                'quote' => 'One request covered three campuses. Install dates were clear and the operators showed up as promised.',
                'role' => 'Operations Director',
                'org' => 'Private school network, CA',
            ],
        ];
    }
}
