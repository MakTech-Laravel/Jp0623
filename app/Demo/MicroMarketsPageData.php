<?php

namespace App\Demo;

class MicroMarketsPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'howItWorks' => self::howItWorks(),
            'equipment' => self::equipment(),
            'offerings' => self::offerings(),
            'compare' => self::compare(),
            'faqs' => self::faqs(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Micro Markets';

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
            'title' => 'Micro market vending',
            'lead' => 'A small self-checkout store in your break room with fresh food, snacks and drinks. Free equipment and setup.',
            'cta' => [
                'label' => 'Get a micro market',
                'href' => '/#request-sec',
            ],
            'art' => 'market',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function howItWorks(): array
    {
        return [
            'eyebrow' => 'How it works',
            'title' => 'Open shelves, self checkout.',
            'steps' => [
                [
                    'title' => 'We design the space',
                    'text' => 'Shelving, coolers and a kiosk sized to your room.',
                ],
                [
                    'title' => 'We stock it',
                    'text' => 'Fresh meals, snacks and drinks chosen for your team.',
                ],
                [
                    'title' => 'Scan and pay',
                    'text' => 'Card, phone or account. We restock from sales data.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function equipment(): array
    {
        return [
            'eyebrow' => 'Equipment',
            'title' => 'What goes in a micro market.',
            'items' => [
                [
                    'title' => 'Open shelves',
                    'text' => 'Snacks, pastries and pantry items people can pick up and read.',
                ],
                [
                    'title' => 'Smart coolers',
                    'text' => 'Drinks, salads and sandwiches kept cold.',
                ],
                [
                    'title' => 'Refrigerated & frozen',
                    'text' => 'Meals, ice cream and frozen options.',
                ],
                [
                    'title' => 'Payment kiosk',
                    'text' => 'Touchscreen self checkout with card, phone and account pay.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function offerings(): array
    {
        $kinds = ['food', 'drink', 'healthy', 'pastry', 'coffee'];
        $catalog = collect(HomePageData::productCatalog())->keyBy('kind');

        return [
            'eyebrow' => 'Food & beverages offered',
            'title' => 'From breakfast to late shift.',
            'items' => collect($kinds)
                ->map(fn (string $kind): ?array => $catalog->get($kind))
                ->filter()
                ->map(fn (array $item): array => [
                    'kind' => $item['kind'],
                    'name' => $item['name'],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function compare(): array
    {
        return [
            'title' => 'Micro markets & AI coolers: we offer both.',
            'items' => [
                [
                    'title' => 'Micro market',
                    'text' => 'Best for 100+ people. The widest selection, including fresh meals.',
                    'art' => 'market',
                    'href' => '/micro-markets',
                ],
                [
                    'title' => 'AI smart cooler',
                    'text' => 'Best for 25–100 people. One cooler that charges for what you take.',
                    'art' => 'cooler',
                    'href' => '/machines/ai-cooler',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function faqs(): array
    {
        return [
            'eyebrow' => 'FAQ',
            'title' => 'Micro market questions.',
            'items' => [
                [
                    'q' => 'How much space does a micro market need?',
                    'a' => 'Most start at about 8 by 10 feet. We size it to your room.',
                ],
                [
                    'q' => 'Is there a cost to the business?',
                    'a' => 'No. Equipment, install and stocking are covered by the operator.',
                ],
                [
                    'q' => 'What about theft?',
                    'a' => 'Kiosks have cameras and most markets have very low loss.',
                ],
                [
                    'q' => 'Do you offer micro markets nationwide?',
                    'a' => 'Yes, through local operators in all 50 states.',
                ],
            ],
        ];
    }
}
