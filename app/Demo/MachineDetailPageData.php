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
                ['label' => 'Payments', 'value' => 'Card, tap, Apple Pay, Google Pay, cash (optional)'],
                ['label' => 'Power', 'value' => '115V dedicated outlet'],
                ['label' => 'Best for', 'value' => $extra['bestFor']],
                ['label' => 'Cost to you', 'value' => '$0 — install, stocking and service included'],
            ],
            'checks' => [
                'Remote stock monitoring',
                'Product mix chosen for your location',
                'Local operator handles repairs',
                'Multiple machines can be placed at locations with more visitors/employees',
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
     * Prototype MACHINES cap / best fields.
     *
     * @return array<string, array{capacity: string, bestFor: string}>
     */
    private static function extras(): array
    {
        return [
            'combo' => [
                'capacity' => 'Up to 35 snack + 40 drink selections',
                'bestFor' => 'Offices and break rooms with 25–150 people',
            ],
            'healthy' => [
                'capacity' => 'Up to 30 snack + 30 drink selections',
                'bestFor' => 'Gyms, hospitals, schools and wellness programs',
            ],
            'snack' => [
                'capacity' => 'Up to 45 selections, 600+ items',
                'bestFor' => 'Warehouses and plants with multiple shifts',
            ],
            'drink' => [
                'capacity' => 'Up to 10 selections, 400+ bottles and cans',
                'bestFor' => 'Lobbies, gyms and outdoor-covered areas',
            ],
            'slim' => [
                'capacity' => 'Up to 20 snack + 12 drink selections',
                'bestFor' => 'Small offices, dealerships and lobbies',
            ],
            'ai-cooler' => [
                'capacity' => 'Up to 250 items',
                'bestFor' => 'Teams of 25–100 who want fresh food and drinks',
            ],
            'micro-market' => [
                'capacity' => 'Up to 500+ items',
                'bestFor' => 'Sites with 100+ people',
            ],
        ];
    }
}
