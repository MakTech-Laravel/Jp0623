<?php

namespace App\Demo;

class ProductsPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Products';

            return $item;
        }, $site['mainNav']);

        return $site;
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'title' => 'Snacks & beverages',
            'lead' => 'Popular brands, healthier picks and local favorites, chosen for each location. Ask for a product and we\'ll add it to your mix.',
            'cta' => [
                'label' => 'Request a free machine',
                'href' => '/#request-sec',
            ],
        ];
    }
}
