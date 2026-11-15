<?php

namespace Database\Seeders;

use App\Models\CoffeeMachine;
use Illuminate\Database\Seeder;

class CoffeeMachineSeeder extends Seeder
{
    /**
     * Seed the demo coffee machines shown on the office coffee page.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $item) {
            CoffeeMachine::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'text' => $item['text'],
                    'tag' => $item['tag'],
                    'image' => $item['image'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return list<array{title: string, text: string, tag: string, image: string}>
     */
    private function items(): array
    {
        return [
            [
                'title' => 'Single-cup brewer',
                'text' => 'Fast, no-mess cups for small teams.',
                'tag' => 'Up to 25 people',
                'image' => '/images/machines/single-cup.jpg',
            ],
            [
                'title' => 'Bean-to-cup machine',
                'text' => 'Fresh-ground espresso, lattes and drip at the touch of a button.',
                'tag' => '25–150 people',
                'image' => '/images/machines/bean-to-cup.jpg',
            ],
            [
                'title' => 'Batch brewer',
                'text' => 'Large pots for meetings and busy kitchens.',
                'tag' => '50+ people',
                'image' => '/images/machines/batch-brewer.jpg',
            ],
        ];
    }
}
