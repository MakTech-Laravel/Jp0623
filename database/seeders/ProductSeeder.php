<?php

namespace Database\Seeders;

use App\Demo\HomePageData;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (HomePageData::productCatalog() as $index => $item) {
            Product::query()->updateOrCreate(
                ['slug' => $item['kind']],
                [
                    'name' => $item['name'],
                    'kind' => $item['kind'],
                    'description' => $item['desc'],
                    'tags' => $item['tags'],
                    'image' => $item['image'],
                    'is_featured' => $index < 5,
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }
    }
}
