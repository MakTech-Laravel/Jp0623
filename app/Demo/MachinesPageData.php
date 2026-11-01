<?php

namespace App\Demo;

class MachinesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'lineup' => self::lineup(),
            'tech' => self::tech(),
            'industries' => self::industries(),
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
            $item['current'] = $item['label'] === 'Machines';

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
            'eyebrow' => 'Free placement',
            'title' => 'Vending machines',
            'lead' => 'Modern snack, drink and combo machines placed, stocked and serviced at no cost to your business.',
            'cta' => [
                'label' => 'Request a free machine',
                'href' => '/#request-sec',
            ],
            'image' => '/images/machines/tradational-vending.png',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function lineup(): array
    {
        return [
            'title' => 'Our machine lineup.',
            'text' => 'Not sure which one fits your space? Tell us your headcount and room size and we\'ll recommend one.',
            'filters' => [
                'All',
                'Combo',
                'Snack',
                'Drink',
                'Healthy',
                'AI cooler',
                'Fresh food',
                'Micro market',
            ],
            'items' => HomePageData::machines(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tech(): array
    {
        return [
            'title' => 'Modern technology, simple experience.',
            'items' => [
                [
                    'title' => 'Cashless pay',
                    'text' => 'Card, phone and app payments on every machine.',
                ],
                [
                    'title' => 'Remote monitoring',
                    'text' => 'Operators see stock levels and restock before machines run out.',
                ],
                [
                    'title' => 'Energy efficient',
                    'text' => 'LED lighting and efficient cooling.',
                ],
                [
                    'title' => 'ADA compliant',
                    'text' => 'Accessible controls and delivery bins.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function industries(): array
    {
        return [
            'eyebrow' => 'By industry',
            'title' => 'Vending for your industry.',
            'items' => [
                [
                    'slug' => 'offices',
                    'label' => 'Offices',
                    'text' => 'Quiet machines, healthy options and coffee for daytime teams.',
                    'href' => '#',
                ],
                [
                    'slug' => 'warehouses',
                    'label' => 'Warehouses',
                    'text' => 'Durable, high-capacity machines for large crews and long shifts.',
                    'href' => '#',
                ],
                [
                    'slug' => 'manufacturing',
                    'label' => 'Manufacturing',
                    'text' => 'Around-the-clock vending that covers every shift on the floor.',
                    'href' => '#',
                ],
                [
                    'slug' => 'schools',
                    'label' => 'Schools',
                    'text' => 'Machines and products that follow school nutrition rules.',
                    'href' => '#',
                ],
                [
                    'slug' => 'colleges',
                    'label' => 'Colleges & universities',
                    'text' => 'Dorms, libraries and student centers with card and phone pay.',
                    'href' => '#',
                ],
                [
                    'slug' => 'hospitals',
                    'label' => 'Hospitals & medical',
                    'text' => '24/7 food and drinks for staff, patients and visitors.',
                    'href' => '#',
                ],
                [
                    'slug' => 'apartments',
                    'label' => 'Apartments',
                    'text' => 'Lobby and clubhouse vending residents can use any time.',
                    'href' => '#',
                ],
                [
                    'slug' => 'hotels',
                    'label' => 'Hotels & motels',
                    'text' => 'Guest-floor drink machines and lobby markets.',
                    'href' => '#',
                ],
                [
                    'slug' => 'gyms',
                    'label' => 'Gyms & fitness',
                    'text' => 'Protein, electrolytes and recovery drinks for members.',
                    'href' => '#',
                ],
                [
                    'slug' => 'retail',
                    'label' => 'Retail & dealerships',
                    'text' => 'Snacks and drinks for customers while they wait.',
                    'href' => '#',
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
            'title' => 'Machine questions.',
            'items' => [
                [
                    'q' => 'Are the machines really free?',
                    'a' => 'Yes. The operator owns the machine and earns from sales, so there is no cost to you.',
                ],
                [
                    'q' => 'How much power do they need?',
                    'a' => 'A standard dedicated 115V outlet near the machine.',
                ],
                [
                    'q' => 'Can I get a machine outside?',
                    'a' => 'Yes, in covered areas with an outdoor-rated machine.',
                ],
                [
                    'q' => 'Which machine should I choose?',
                    'a' => 'Use the request form and we\'ll recommend one based on your size and space.',
                ],
            ],
        ];
    }
}
