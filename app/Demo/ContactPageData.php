<?php

namespace App\Demo;

class ContactPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'panel' => self::panel(),
            'form' => self::form(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = HomePageData::site();

        $site['mainNav'] = array_map(function (array $item): array {
            $item['current'] = $item['label'] === 'Contact Us';

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
            'title' => 'Contact us',
            'lead' => 'Complete the form below or email our team.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function panel(): array
    {
        return [
            'title' => 'Talk to our team',
            'email' => 'hello@vendingmachinelocationsforsale.com',
            'emailHref' => 'mailto:hello@vendingmachinelocationsforsale.com',
            'hours' => 'Mon–Fri, 8am–6pm ET',
            'operatorTitle' => 'Vending operator?',
            'operatorLinks' => [
                [
                    'label' => 'operator page',
                    'href' => '/operators',
                ],
                [
                    'label' => 'log in',
                    'href' => '/login',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function form(): array
    {
        return [
            'successMessage' => 'Message sent. We\'ll reply within one business day.',
            'submitLabel' => 'Send message',
            'topics' => [
                'Getting vending for my business',
                'Service on an existing machine',
                'Becoming an operator',
                'Something else',
            ],
        ];
    }
}
