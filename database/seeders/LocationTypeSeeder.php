<?php

namespace Database\Seeders;

use App\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationTypeSeeder extends Seeder
{
    /**
     * Seed the default vending-request location types.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $name) {
            LocationType::query()->updateOrCreate(
                ['name' => $name],
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
            'Business / office',
            'Warehouse / factory',
            'Apartment / hotel',
            'School / college',
            'Medical',
            'Gym',
            'Other',
        ];
    }
}
