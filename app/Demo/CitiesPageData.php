<?php

namespace App\Demo;

class CitiesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function index(): array
    {
        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Cities we serve',
                'lead' => 'Local vending service in every state. These cities have dedicated local pages.',
            ],
            'items' => self::catalog(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function show(string $slug): ?array
    {
        $catalog = collect(self::catalog());
        $city = $catalog->firstWhere('slug', $slug);

        if ($city === null) {
            return null;
        }

        $name = $city['city'];
        $state = $city['state'];

        $nearby = $catalog
            ->reject(fn (array $item): bool => $item['slug'] === $slug)
            ->sortBy(fn (array $item): int => $item['state'] === $state ? 0 : 1)
            ->take(5)
            ->values()
            ->map(fn (array $item): array => [
                'label' => $item['city'],
                'href' => $item['href'],
            ])
            ->all();

        $requestForm = HomePageData::requestForm();
        $requestForm['eyebrow'] = 'Free request';
        $requestForm['title'] = 'Request vending in '.$name.'.';

        return [
            'site' => self::site(),
            'hero' => [
                'eyebrow' => $name.', '.$state,
                'title' => 'Vending services in '.$name,
                'lead' => 'Free vending machines, micro markets and office coffee for '.$name.' businesses. Local operators install and service every machine.',
                'cta' => [
                    'label' => 'Request a free machine',
                    'href' => '#request-sec',
                ],
                'art' => 'combo',
            ],
            'crumb' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Cities', 'href' => '/cities'],
                ['label' => $name, 'href' => $city['href']],
            ],
            'city' => [
                'slug' => $city['slug'],
                'city' => $name,
                'state' => $state,
            ],
            'benefits' => [
                'title' => 'Why '.$name.' businesses choose us.',
                'items' => [
                    [
                        'title' => 'Local operators',
                        'text' => 'Vetted vending companies based in the '.$name.' area.',
                    ],
                    [
                        'title' => 'Fast install',
                        'text' => 'Most '.$name.' placements are installed in 2–3 weeks.',
                    ],
                    [
                        'title' => 'Every industry',
                        'text' => 'Offices, warehouses, apartments, gyms and more across '.$state.'.',
                    ],
                ],
            ],
            'requestForm' => $requestForm,
            'faqs' => [
                'eyebrow' => 'FAQ',
                'title' => 'Vending in '.$name.': questions.',
                'items' => [
                    [
                        'q' => 'Do you serve all of '.$name.'?',
                        'a' => 'Yes, including surrounding suburbs and nearby cities in '.$state.'.',
                    ],
                    [
                        'q' => 'Is it really free?',
                        'a' => 'Yes. The operator owns and services the machine.',
                    ],
                    [
                        'q' => 'How fast can you install?',
                        'a' => 'Usually 2 to 4 weeks after your request is confirmed.',
                    ],
                ],
            ],
            'nearby' => $nearby,
        ];
    }

    /**
     * @return list<array{city: string, state: string, label: string, slug: string, href: string}>
     */
    public static function catalog(): array
    {
        return HomePageData::cities()['items'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['topNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Cities';

            return $item;
        }, $site['topNav']);

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = false;

            return $item;
        }, $site['mainNav']);

        return $site;
    }
}
