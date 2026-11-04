<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineCategory;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Seed the demo machine catalog for admin management.
     */
    public function run(): void
    {
        $categories = MachineCategory::query()->pluck('id', 'name');

        $featured = ['combo', 'healthy', 'ai-cooler'];

        foreach ($this->machines() as $index => $machine) {
            $categoryId = $categories[$machine['type']] ?? null;

            if ($categoryId === null) {
                continue;
            }

            Machine::query()->updateOrCreate(
                ['slug' => $machine['id']],
                [
                    'machine_category_id' => $categoryId,
                    'name' => $machine['name'],
                    'kind' => $machine['kind'],
                    'tags' => $machine['tags'],
                    'description' => $machine['desc'],
                    'image' => $machine['image'],
                    'capacity' => $machine['capacity'],
                    'dimensions' => $machine['dimensions'],
                    'best_for' => $machine['best_for'],
                    'status' => 'Available nationwide',
                    'is_featured' => in_array($machine['id'], $featured, true),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     kind: string,
     *     type: string,
     *     tags: list<string>,
     *     desc: string,
     *     image: string,
     *     capacity: string,
     *     dimensions: string,
     *     best_for: string
     * }>
     */
    private function machines(): array
    {
        return [
            [
                'id' => 'combo',
                'name' => 'Snack & Drink Combo',
                'kind' => 'combo',
                'type' => 'Combo',
                'tags' => ['Combo', 'Card + phone'],
                'desc' => 'One machine for snacks and cold drinks. The most common choice for a first placement.',
                'image' => '/images/machines/Alpine-ST5000.png',
                'capacity' => '35 snack + 40 drink selections',
                'dimensions' => '72" H × 39" W × 35" D',
                'best_for' => 'Offices and break rooms with 25–150 people',
            ],
            [
                'id' => 'healthy',
                'name' => 'Healthy Choice Combo',
                'kind' => 'healthy',
                'type' => 'Healthy',
                'tags' => ['Healthy', 'Combo'],
                'desc' => 'Stocked with better-for-you snacks, waters and low-sugar drinks, with nutrition labels.',
                'image' => '/images/machines/healthy-vending.png',
                'capacity' => '30 snack + 30 drink selections',
                'dimensions' => '72" H × 39" W × 35" D',
                'best_for' => 'Gyms, hospitals, schools and wellness programs',
            ],
            [
                'id' => 'snack',
                'name' => 'Glass-Front Snack',
                'kind' => 'snack',
                'type' => 'Snack',
                'tags' => ['Snack', 'High capacity'],
                'desc' => 'A large snack selection that holds more product between service visits.',
                'image' => '/images/machines/tradational-vending.png',
                'capacity' => '45 selections, 600+ items',
                'dimensions' => '72" H × 41" W × 35" D',
                'best_for' => 'Warehouses and plants with multiple shifts',
            ],
            [
                'id' => 'drink',
                'name' => 'Cold Drink Machine',
                'kind' => 'drink',
                'type' => 'Drink',
                'tags' => ['Drinks'],
                'desc' => 'Water, soda, juice, tea and energy drinks kept cold all day.',
                'image' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
                'capacity' => '10 selections, 400+ bottles and cans',
                'dimensions' => '72" H × 38" W × 33" D',
                'best_for' => 'Lobbies, gyms and outdoor-covered areas',
            ],
            [
                'id' => 'touch',
                'name' => 'Touchscreen Combo',
                'kind' => 'touch',
                'type' => 'Combo',
                'tags' => ['Combo', 'Touchscreen'],
                'desc' => 'A large screen with product photos, nutrition info and promotions.',
                'image' => '/images/machines/micro-market.png',
                'capacity' => '40 snack + 20 drink selections',
                'dimensions' => '76" H × 40" W × 36" D',
                'best_for' => 'Corporate offices and showrooms',
            ],
            [
                'id' => 'food',
                'name' => 'Refrigerated Fresh Food',
                'kind' => 'food',
                'type' => 'Fresh food',
                'tags' => ['Fresh food'],
                'desc' => 'Sandwiches, salads and meals held at safe temperatures with health controls.',
                'image' => '/images/machines/coffee.png',
                'capacity' => '30 selections',
                'dimensions' => '72" H × 40" W × 35" D',
                'best_for' => 'Hospitals, night shifts and sites without a cafeteria',
            ],
            [
                'id' => 'slim',
                'name' => 'Space-Saver Combo',
                'kind' => 'slim',
                'type' => 'Combo',
                'tags' => ['Combo', 'Compact'],
                'desc' => 'A slim unit that fits where a full-size machine won\'t.',
                'image' => '/images/machines/Alpine-ST5000.png',
                'capacity' => '20 snack + 12 drink selections',
                'dimensions' => '72" H × 30" W × 33" D',
                'best_for' => 'Small offices, dealerships and lobbies',
            ],
            [
                'id' => 'ai-cooler',
                'name' => 'Vendera Smart Cooler',
                'kind' => 'cooler',
                'type' => 'Vendera cooler',
                'tags' => ['Vendera cooler', 'Tap and go'],
                'desc' => 'Tap a card or phone, open the door and take what you want. Cameras and weight sensors charge only for what you took.',
                'image' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
                'capacity' => 'Up to 250 items',
                'dimensions' => '80" H × 31" W × 32" D',
                'best_for' => 'Teams of 25–100 who want fresh food and drinks',
            ],
            [
                'id' => 'micro-market',
                'name' => 'Micro Market Kiosk',
                'kind' => 'market',
                'type' => 'Micro market',
                'tags' => ['Micro market', 'Self checkout'],
                'desc' => 'Open shelves, coolers and a self-checkout kiosk: a small store in your break room.',
                'image' => '/images/machines/micro-market.png',
                'capacity' => '500+ items',
                'dimensions' => 'Fits rooms from 8 × 10 ft',
                'best_for' => 'Sites with 100+ people',
            ],
        ];
    }
}
