<?php

namespace App\Demo;

class ResourcesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function index(): array
    {
        $items = self::cards();

        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Vending & office coffee resource hub',
                'lead' => 'Guides for businesses choosing vending and for operators growing their routes.',
            ],
            'filters' => ['All', ...collect($items)->pluck('cat')->unique()->values()->all()],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function show(string $slug): ?array
    {
        $catalog = collect(self::catalog());
        $article = $catalog->firstWhere('id', $slug);

        if ($article === null) {
            return null;
        }

        $related = $catalog
            ->reject(fn (array $item): bool => $item['id'] === $slug)
            ->take(3)
            ->map(fn (array $item): array => collect($item)->except('body')->all())
            ->values()
            ->all();

        return [
            'site' => self::site(),
            'crumb' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Resources', 'href' => '/resources'],
                ['label' => $article['cat'], 'href' => '/resources'],
            ],
            'article' => [
                'id' => $article['id'],
                'cat' => $article['cat'],
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'readTime' => '5 min read',
                'updated' => 'Updated Nov 2026',
            ],
            'cta' => [
                'title' => 'Need vending for your location?',
                'text' => 'Free machine, install and service.',
                'button' => [
                    'label' => 'Request a free machine',
                    'href' => '/#request-sec',
                ],
            ],
            'related' => $related,
        ];
    }

    /**
     * Article cards without the body text.
     *
     * @return list<array{id: string, cat: string, title: string, excerpt: string, href: string}>
     */
    public static function cards(): array
    {
        return array_map(
            fn (array $article): array => collect($article)->except('body')->all(),
            self::catalog(),
        );
    }

    /**
     * @return list<array{id: string, cat: string, title: string, excerpt: string, href: string, body: list<string>}>
     */
    public static function catalog(): array
    {
        $articles = [
            [
                'id' => 'multi-site',
                'cat' => 'Vending management',
                'title' => 'How to choose a vending company for multiple sites',
                'excerpt' => 'What to ask before you sign, and why one contact beats ten vendors.',
                'body' => [
                    'Managing vending across many buildings gets messy fast. Each site ends up with a different vendor, a different contract and a different phone number to call when a machine breaks.',
                    'Start by asking how the company handles service calls, how often machines are restocked, and whether you get one report for every location.',
                    'A management company gives you one contact and one standard, while local operators do the day-to-day work.',
                ],
            ],
            [
                'id' => 'ai-vs-market',
                'cat' => 'Smart & AI vending',
                'title' => 'Vendera coolers vs. micro markets: which one fits?',
                'excerpt' => 'A simple guide based on headcount, space and food needs.',
                'body' => [
                    'Vendera smart coolers work well for 25 to 100 people. They take up the space of one machine and can hold fresh food.',
                    'Micro markets need a small room or corner but offer the widest selection, including meals and coffee.',
                    'If you are not sure, start with a cooler. It is easy to upgrade to a market later.',
                ],
            ],
            [
                'id' => 'healthy-mix',
                'cat' => 'Healthy vending',
                'title' => 'Building a healthier snack mix your team will buy',
                'excerpt' => 'Swap in better options without killing sales.',
                'body' => [
                    'Healthy vending works when people still find what they like. Replace about a third of the slots first, then check sales after a month.',
                    'Water, nuts, protein bars and baked chips tend to sell well. Label them clearly on the machine.',
                    'Keep a few favorites so the machine stays popular.',
                ],
            ],
            [
                'id' => 'school-rules',
                'cat' => 'Industry guides',
                'title' => 'School vending: what the nutrition rules mean for you',
                'excerpt' => 'Smart Snacks standards in plain English.',
                'body' => [
                    'Snacks sold to students during the school day must meet federal nutrition standards for calories, sugar, fat and sodium.',
                    'Staff-only machines in break rooms are not covered by the student rules.',
                    'Ask your operator for a compliant product list before the machine is stocked.',
                ],
            ],
            [
                'id' => 'coffee-budget',
                'cat' => 'Office coffee',
                'title' => 'Setting a monthly office coffee budget',
                'excerpt' => 'How much coffee a team actually drinks, and how to plan for it.',
                'body' => [
                    'Plan on one to two cups per person per day for most offices.',
                    'Bean-to-cup machines cost more per cup than single-cup brewers but are popular with larger teams.',
                    'Set a monthly cap and review usage every quarter.',
                ],
            ],
            [
                'id' => 'operator-growth',
                'cat' => 'Operator growth',
                'title' => 'Five ways vending operators find new locations',
                'excerpt' => 'Lead services, referrals, routes for sale and more.',
                'body' => [
                    'Verified lead services save time because the business has already asked for vending.',
                    'Buying an existing route gets you revenue on day one.',
                    'Referrals from current locations remain one of the best sources of new accounts.',
                ],
            ],
        ];

        return array_map(function (array $article): array {
            $article['href'] = '/resources/'.$article['id'];

            return $article;
        }, $articles);
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['topNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Resources';

            return $item;
        }, $site['topNav']);

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = false;

            return $item;
        }, $site['mainNav']);

        return $site;
    }
}
