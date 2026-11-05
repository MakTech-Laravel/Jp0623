<?php

namespace App\Demo;

class IndustriesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function index(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'items' => array_values(array_map(
                fn (array $industry): array => [
                    'title' => $industry['label'],
                    'text' => $industry['text'],
                    'image' => $industry['image'],
                    'href' => '/industries/'.$industry['slug'],
                    'slug' => $industry['slug'],
                ],
                self::catalog(),
            )),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function show(string $slug): ?array
    {
        $industry = self::catalog()[$slug] ?? null;

        if ($industry === null) {
            return null;
        }

        $machines = collect(HomePageData::machines())
            ->filter(function (array $machine) use ($industry): bool {
                return in_array($machine['kind'], [$industry['kind'], 'combo', 'cooler'], true)
                    || $machine['id'] === $industry['kind'];
            })
            ->take(3)
            ->values();

        if ($machines->isEmpty()) {
            $machines = collect(HomePageData::machines())->take(3)->values();
        }

        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Vending for '.strtolower($industry['label']),
                'lead' => $industry['text'].' Free equipment, install and service.',
                'cta' => [
                    'label' => 'Request a free machine',
                    'href' => '#industry-request',
                ],
                'image' => $industry['image'],
            ],
            'crumb' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Industries', 'href' => '/industries'],
                ['label' => $industry['label'], 'href' => '/industries/'.$industry['slug']],
            ],
            'industry' => [
                'slug' => $industry['slug'],
                'label' => $industry['label'],
            ],
            'benefits' => [
                'title' => 'What your location gets.',
                'items' => [
                    [
                        'title' => 'A plan for your hours',
                        'text' => 'Restocking timed around your shifts and busy days.',
                    ],
                    [
                        'title' => 'The right products',
                        'text' => 'A mix based on what your people buy.',
                    ],
                    [
                        'title' => 'One point of contact',
                        'text' => 'Email us or use the contact page for service at any of your sites.',
                    ],
                ],
            ],
            'relatedMachines' => [
                'title' => 'Machines that work well here.',
                'items' => $machines->all(),
            ],
            'requestForm' => [
                'title' => 'Request vending for your '.rtrim(strtolower($industry['label']), 's').'.',
                'text' => 'Tell us about your location and we\'ll match you with the right setup.',
                'successMessage' => 'Thanks! We\'ll follow up within one business day.',
                'submitLabel' => 'Send request',
                'subjectLabel' => 'location',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['topNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Industries';

            return $item;
        }, $site['topNav']);

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = false;

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
            'title' => 'Industries we serve',
            'lead' => 'Vending, coffee and micro markets set up for how your location works.',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function catalog(): array
    {
        $items = [
            [
                'slug' => 'offices',
                'label' => 'Offices',
                'text' => 'Quiet machines, healthy options and coffee for daytime teams.',
                'kind' => 'combo',
                'image' => '/images/machines/Alpine-ST5000.jpg',
            ],
            [
                'slug' => 'warehouses',
                'label' => 'Warehouses',
                'text' => 'Durable, high-capacity machines for large crews and long shifts.',
                'kind' => 'snack',
                'image' => '/images/machines/tradational-vending.png',
            ],
            [
                'slug' => 'manufacturing',
                'label' => 'Manufacturing',
                'text' => 'Around-the-clock vending that covers every shift on the floor.',
                'kind' => 'food',
                'image' => '/images/machines/Alpine-ST5000.jpg',
            ],
            [
                'slug' => 'schools',
                'label' => 'Schools',
                'text' => 'Machines and products that follow school nutrition rules.',
                'kind' => 'healthy',
                'image' => '/images/machines/healthy-vending.png',
            ],
            [
                'slug' => 'colleges',
                'label' => 'Colleges & universities',
                'text' => 'Dorms, libraries and student centers with card and phone pay.',
                'kind' => 'touch',
                'image' => '/images/machines/micro-market.png',
            ],
            [
                'slug' => 'hospitals',
                'label' => 'Hospitals & medical',
                'text' => '24/7 food and drinks for staff, patients and visitors.',
                'kind' => 'food',
                'image' => '/images/machines/coffee.png',
            ],
            [
                'slug' => 'apartments',
                'label' => 'Apartments',
                'text' => 'Lobby and clubhouse vending residents can use any time.',
                'kind' => 'cooler',
                'image' => '/images/machines/ai-cooler.png',
            ],
            [
                'slug' => 'hotels',
                'label' => 'Hotels & motels',
                'text' => 'Guest-floor drink machines and lobby markets.',
                'kind' => 'drink',
                'image' => '/images/machines/ai-cooler.png',
            ],
            [
                'slug' => 'gyms',
                'label' => 'Gyms & fitness',
                'text' => 'Protein, electrolytes and recovery drinks for members.',
                'kind' => 'healthy',
                'image' => '/images/machines/healthy-vending.png',
            ],
            [
                'slug' => 'retail',
                'label' => 'Retail & dealerships',
                'text' => 'Snacks and drinks for customers while they wait.',
                'kind' => 'slim',
                'image' => '/images/machines/Alpine-ST5000.jpg',
            ],
        ];

        $catalog = [];

        foreach ($items as $item) {
            $catalog[$item['slug']] = $item;
        }

        return $catalog;
    }
}
