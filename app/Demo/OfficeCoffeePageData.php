<?php

namespace App\Demo;

class OfficeCoffeePageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'machines' => self::machines(),
            'types' => self::types(),
            'service' => self::service(),
            'requestForm' => self::requestForm(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Office Coffee';

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
            'title' => 'Office coffee service',
            'lead' => 'Free brewers, delivery and installation, with supplies restocked on your schedule.',
            'cta' => [
                'label' => 'Get coffee service',
                'href' => '#coffee-form',
            ],
            'image' => '/images/machines/coffee1.png',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function machines(): array
    {
        return [
            'eyebrow' => 'Coffee machines',
            'title' => 'Equipment for every office.',
            'items' => [
                [
                    'title' => 'Single-cup brewer',
                    'text' => 'Fast, no-mess cups for small teams.',
                    'tag' => 'Up to 25 people',
                    'image' => '/images/machines/single-cup.jpg',
                    'href' => '#coffee-form',
                ],
                [
                    'title' => 'Bean-to-cup machine',
                    'text' => 'Fresh-ground espresso, lattes and drip at the touch of a button.',
                    'tag' => '25–150 people',
                    'image' => '/images/machines/bean-to-cup.jpg',
                    'href' => '#coffee-form',
                ],
                [
                    'title' => 'Batch brewer',
                    'text' => 'Large pots for meetings and busy kitchens.',
                    'tag' => '50+ people',
                    'image' => '/images/machines/batch-brewer.jpg',
                    'href' => '#coffee-form',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function types(): array
    {
        $labels = [
            'Medium roast',
            'Dark roast',
            'Decaf',
            'Espresso',
            'Cold brew',
            'Flavored coffee',
            'Green & black tea',
            'Hot cocoa',
            'Creamers & sweeteners',
        ];

        return [
            'eyebrow' => 'Coffee types',
            'title' => 'What we stock.',
            'items' => array_map(
                fn (string $label): array => [
                    'label' => $label,
                    'href' => '#coffee-form',
                ],
                $labels,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function service(): array
    {
        return [
            'eyebrow' => 'Service',
            'title' => 'Full-service coffee at no equipment cost.',
            'items' => [
                [
                    'title' => 'Free equipment',
                    'text' => 'Install and maintenance included.',
                    'href' => '#coffee-form',
                ],
                [
                    'title' => 'Delivery & restocking',
                    'text' => 'Weekly or monthly, within your budget.',
                    'href' => '#coffee-form',
                ],
                [
                    'title' => 'More than coffee',
                    'text' => 'Cups, filtered water, snacks and cleaning supplies.',
                    'href' => '#coffee-form',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestForm(): array
    {
        return [
            'title' => 'Request coffee service',
            'text' => 'Tell us about your office and we\'ll send options.',
            'successMessage' => 'Thanks! We\'ll send coffee options within one business day.',
            'submitLabel' => 'Send request',
            'subjectLabel' => 'office',
        ];
    }
}
