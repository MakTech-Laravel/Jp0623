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
            'site' => self::site(),
            'hero' => self::hero(),
            'audiences' => self::audiences(),
            'products' => self::products(),
            'guide' => self::guide(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        return HomePageData::site();
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
                'label' => 'Get Vending Now',
                'href' => '/#request-sec',
            ],
            'image' => '/images/machines/healthy-vending.png',
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
        return [
            'eyebrow' => 'Products',
            'title' => 'School-friendly picks.',
            // Populated from Product records in SchoolsController.
            'items' => [],
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
            // Populated from the articles table in SchoolsController.
            'item' => null,
        ];
    }
}
