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
            'text' => 'Availability of machines and or micro markets may vary by region. Multiple machines can be placed at locations with more visitors/employees.',
            'filters' => ['All'],
            'items' => [],
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
            // Populated from active, public LocationType records in MachinesController.
            'items' => [],
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
