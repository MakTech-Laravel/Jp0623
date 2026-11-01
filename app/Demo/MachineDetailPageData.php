<?php

namespace App\Demo;

class MachineDetailPageData
{
    /**
     * @return array<string, mixed>|null
     */
    public static function all(string $id): ?array
    {
        $machine = collect(HomePageData::machines())->firstWhere('id', $id);

        if ($machine === null) {
            return null;
        }

        $extra = self::extras()[$id] ?? null;

        if ($extra === null) {
            return null;
        }

        return [
            'site' => MachinesPageData::site(),
            'machine' => $machine,
            'status' => 'Available nationwide',
            'specs' => [
                ['label' => 'Capacity', 'value' => $extra['capacity']],
                ['label' => 'Dimensions', 'value' => $extra['dimensions']],
                ['label' => 'Payments', 'value' => 'Card, tap, Apple Pay, Google Pay, cash (optional)'],
                ['label' => 'Power', 'value' => '115V dedicated outlet'],
                ['label' => 'Best for', 'value' => $extra['bestFor']],
                ['label' => 'Cost to you', 'value' => '$0 — install, stocking and service included'],
            ],
            'checks' => [
                'Remote stock monitoring',
                'Product mix chosen for your location',
                'Local operator handles repairs',
            ],
            'related' => self::related($id),
            'cta' => [
                'label' => 'Request this machine',
                'href' => '/#request-sec',
            ],
        ];
    }

    /**
     * @return array{title: string, items: list<array<string, mixed>>}
     */
    public static function related(string $id): array
    {
        return [
            'title' => 'Other machines you might like.',
            'items' => collect(HomePageData::machines())
                ->reject(fn (array $machine): bool => $machine['id'] === $id)
                ->take(3)
                ->values()
                ->all(),
        ];
    }

    /**
     * Prototype MACHINES cap / dim / best fields.
     *
     * @return array<string, array{capacity: string, dimensions: string, bestFor: string}>
     */
    private static function extras(): array
    {
        return [
            'combo' => [
                'capacity' => '35 snack + 40 drink selections',
                'dimensions' => '72" H × 39" W × 35" D',
                'bestFor' => 'Offices and break rooms with 25–150 people',
            ],
            'healthy' => [
                'capacity' => '30 snack + 30 drink selections',
                'dimensions' => '72" H × 39" W × 35" D',
                'bestFor' => 'Gyms, hospitals, schools and wellness programs',
            ],
            'snack' => [
                'capacity' => '45 selections, 600+ items',
                'dimensions' => '72" H × 41" W × 35" D',
                'bestFor' => 'Warehouses and plants with multiple shifts',
            ],
            'drink' => [
                'capacity' => '10 selections, 400+ bottles and cans',
                'dimensions' => '72" H × 38" W × 33" D',
                'bestFor' => 'Lobbies, gyms and outdoor-covered areas',
            ],
            'touch' => [
                'capacity' => '40 snack + 20 drink selections',
                'dimensions' => '76" H × 40" W × 36" D',
                'bestFor' => 'Corporate offices and showrooms',
            ],
            'food' => [
                'capacity' => '30 selections',
                'dimensions' => '72" H × 40" W × 35" D',
                'bestFor' => 'Hospitals, night shifts and sites without a cafeteria',
            ],
            'slim' => [
                'capacity' => '20 snack + 12 drink selections',
                'dimensions' => '72" H × 30" W × 33" D',
                'bestFor' => 'Small offices, dealerships and lobbies',
            ],
            'ai-cooler' => [
                'capacity' => 'Up to 250 items',
                'dimensions' => '80" H × 31" W × 32" D',
                'bestFor' => 'Teams of 25–100 who want fresh food and drinks',
            ],
            'micro-market' => [
                'capacity' => '500+ items',
                'dimensions' => 'Fits rooms from 8 × 10 ft',
                'bestFor' => 'Sites with 100+ people',
            ],
        ];
    }
}
