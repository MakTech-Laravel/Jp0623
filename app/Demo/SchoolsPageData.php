<?php

namespace App\Demo;

class SchoolsPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => HomePageData::site(),
            'hero' => self::hero(),
            'audiences' => self::audiences(),
            'products' => self::products(),
            'guide' => self::guide(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'title' => 'School vending',
            'lead' => 'Vending for K-12 schools, colleges and universities, with products that follow nutrition rules for students.',
            'cta' => [
                'label' => 'Request a free machine',
                'href' => '/#request-sec',
            ],
            'art' => 'healthy',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function audiences(): array
    {
        return [
            'title' => 'Two audiences, two setups.',
            'items' => [
                [
                    'title' => 'Students',
                    'text' => 'Smart Snacks–compliant products: water, juice, baked snacks and low-sugar options during the school day.',
                ],
                [
                    'title' => 'Faculty & staff',
                    'text' => 'Full product range plus coffee in staff lounges.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function products(): array
    {
        $kinds = ['healthy', 'bars', 'nuts', 'drink', 'food'];
        $catalog = collect(HomePageData::productCatalog())->keyBy('kind');

        return [
            'eyebrow' => 'Products',
            'title' => 'School-friendly picks.',
            'items' => collect($kinds)
                ->map(fn (string $kind): ?array => $catalog->get($kind))
                ->filter()
                ->map(fn (array $item): array => [
                    'kind' => $item['kind'],
                    'name' => $item['name'],
                    'image' => $item['image'],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function guide(): array
    {
        return [
            'eyebrow' => 'Guide',
            'title' => 'Learn the rules.',
            'link' => [
                'label' => 'View all resources →',
                'href' => '#',
            ],
            'items' => [
                [
                    'id' => 'school-rules',
                    'cat' => 'Industry guides',
                    'title' => 'School vending: what the nutrition rules mean for you',
                    'excerpt' => 'Smart Snacks standards in plain English.',
                    'href' => '#',
                ],
            ],
        ];
    }
}
