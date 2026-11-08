<?php

namespace App\Demo;

use InvalidArgumentException;

class LegalPageData
{
    /**
     * @return array<string, array{title: string, sections: list<string>}>
     */
    public static function pages(): array
    {
        return [
            'privacy' => [
                'title' => 'Privacy policy',
                'sections' => [
                    'Information we collect',
                    'How we use information',
                    'Sharing with vending operators',
                    'Cookies and tracking',
                    'Your choices and rights',
                    'Contact us',
                ],
            ],
            'terms' => [
                'title' => 'Terms & conditions',
                'sections' => [
                    'Using this website',
                    'Service requests',
                    'Location terms',
                    'Limits of liability',
                    'Changes to these terms',
                ],
            ],
            'operator-terms' => [
                'title' => 'Operator terms',
                'sections' => [
                    'Membership and billing',
                    'Lead purchases',
                    'Lead issue reports',
                    'Routes and third-party brokers',
                    'Account termination',
                ],
            ],
            'cookies' => [
                'title' => 'Cookie policy',
                'sections' => [
                    'Essential cookies',
                    'Analytics cookies',
                    'Advertising cookies',
                    'Do not sell or share my personal information',
                    'Changing your choices',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function for(string $page): array
    {
        $pages = self::pages();

        if (! isset($pages[$page])) {
            throw new InvalidArgumentException("Unknown legal page [{$page}].");
        }

        $data = $pages[$page];

        return [
            'site' => HomePageData::site(),
            'page' => $page,
            'hero' => [
                'title' => $data['title'],
                'lead' => 'Last updated November 1, 2026.',
            ],
            'banner' => "Placeholder: the client's lawyer supplies the final text for this page.",
            'sections' => array_map(
                fn (string $heading): array => [
                    'heading' => $heading,
                    'body' => 'This section will explain '.strtolower($heading).' in plain language for businesses and operators using the site.',
                ],
                $data['sections'],
            ),
        ];
    }
}
