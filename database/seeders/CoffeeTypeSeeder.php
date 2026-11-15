<?php

namespace Database\Seeders;

use App\Models\CoffeeType;
use Illuminate\Database\Seeder;

class CoffeeTypeSeeder extends Seeder
{
    /**
     * Seed the demo coffee types shown in "What we stock." on the office coffee page.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $label) {
            CoffeeType::query()->updateOrCreate(
                ['label' => $label],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return list<string>
     */
    private function items(): array
    {
        return [
            'Medium roast',
            'Dark roast',
            'Decaf',
            'Espresso',
            'Cold brew',
            'Flavored coffee',
            'Green & black tea',
            'Hot cocoa',
            'Creamers & sweeteners',
        ];
    }
}
