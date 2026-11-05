<?php

namespace App\Demo;

class AboutPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'story' => self::story(),
            'cta' => self::cta(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['topNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'About';

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
            'title' => 'About us',
            'lead' => 'We make vending simple for businesses with one location or hundreds, and we help independent operators grow.',
            'image' => '/images/machines/Alpine-ST5000.jpg',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function story(): array
    {
        return [
            'title' => 'One contact for vending, everywhere',
            'paragraphs' => [
                'Most businesses don\'t want to manage vending vendors. We take that work off their plate: we take the request, check the details, and match each site with a vetted local operator.',
                'On the other side, we give independent operators a steady flow of verified locations, routes for sale and tools to grow their business.',
            ],
            'valuesTitle' => 'What we stand for',
            'values' => [
                'No cost to the location',
                'Local operators who respond quickly',
                'Clear rules for operators: verified leads, one buyer per lead',
            ],
            'kpis' => [
                [
                    'label' => 'States covered',
                    'value' => '50',
                ],
                [
                    'label' => 'Cities with local pages',
                    'value' => '30+',
                ],
                [
                    'label' => 'Operators in network',
                    'value' => '1,000+',
                ],
                [
                    'label' => 'Machine types',
                    'value' => '9',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function cta(): array
    {
        return [
            'title' => 'Ready to get started?',
            'primary' => [
                'label' => 'Request a free machine',
                'href' => '/#request-sec',
            ],
            'secondary' => [
                'label' => "I'm a vending operator",
                'href' => '#',
            ],
        ];
    }
}
