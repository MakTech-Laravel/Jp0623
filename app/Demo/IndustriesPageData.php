<?php

namespace App\Demo;

use App\Models\LocationType;

class IndustriesPageData
{
    /**
     * @param  list<LocationType>  $industries
     * @return array<string, mixed>
     */
    public static function index(array $industries): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'items' => array_map(
                fn (LocationType $industry): array => [
                    'title' => $industry->name,
                    'text' => $industry->description ?? '',
                    'image' => $industry->image_url ?? '',
                    'href' => '/industries/'.$industry->slug,
                    'slug' => (string) $industry->slug,
                ],
                $industries,
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $allMachines  Active machines from MachineService::listForFrontend()
     * @return array<string, mixed>
     */
    public static function show(LocationType $industry, array $allMachines): array
    {
        $kind = $industry->kind;

        $machines = collect($allMachines)
            ->filter(function (array $machine) use ($kind): bool {
                return $kind !== null && (
                    in_array($machine['kind'], [$kind, 'combo', 'cooler'], true)
                    || $machine['id'] === $kind
                );
            })
            ->take(3)
            ->values();

        if ($machines->isEmpty()) {
            $machines = collect($allMachines)->take(3)->values();
        }

        $text = $industry->description ?? '';

        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Vending for '.strtolower($industry->name),
                'lead' => trim($text.' Free equipment, install and service.'),
                'cta' => [
                    'label' => 'Request a free machine',
                    'href' => '#industry-request',
                ],
                'image' => $industry->image_url ?? '',
            ],
            'crumb' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Industries', 'href' => '/industries'],
                ['label' => $industry->name, 'href' => '/industries/'.$industry->slug],
            ],
            'industry' => [
                'slug' => (string) $industry->slug,
                'label' => $industry->name,
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
                'title' => 'Request vending for your location.',
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
}
