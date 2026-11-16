<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['Houston', 'TX'],
            ['New York', 'NY'],
            ['Atlanta', 'GA'],
            ['Dallas', 'TX'],
            ['Chicago', 'IL'],
            ['Miami', 'FL'],
            ['Charlotte', 'NC'],
            ['Philadelphia', 'PA'],
            ['Los Angeles', 'CA'],
            ['Fort Worth', 'TX'],
            ['Orlando', 'FL'],
            ['Phoenix', 'AZ'],
            ['Tampa', 'FL'],
            ['Las Vegas', 'NV'],
            ['Indianapolis', 'IN'],
            ['Cleveland', 'OH'],
            ['Memphis', 'TN'],
            ['Fort Lauderdale', 'FL'],
            ['Sacramento', 'CA'],
            ['Detroit', 'MI'],
            ['Boston', 'MA'],
            ['Jacksonville', 'FL'],
            ['Columbus', 'OH'],
            ['Raleigh', 'NC'],
            ['Washington', 'DC'],
            ['Baltimore', 'MD'],
            ['Cincinnati', 'OH'],
            ['Greensboro', 'NC'],
            ['San Antonio', 'TX'],
            ['Nashville', 'TN'],
        ];

        foreach ($cities as $index => [$city, $state]) {
            City::query()->updateOrCreate(
                ['city' => $city, 'state' => $state],
                ['sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
