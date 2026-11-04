<?php

namespace Database\Seeders;

use App\Models\MachineCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MachineCategorySeeder extends Seeder
{
    /**
     * Seed machine categories from the public lineup filters.
     */
    public function run(): void
    {
        $categories = [
            'Combo',
            'Snack',
            'Drink',
            'Healthy',
            'Vendera cooler',
            'Fresh food',
            'Micro market',
        ];

        foreach ($categories as $index => $name) {
            MachineCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
