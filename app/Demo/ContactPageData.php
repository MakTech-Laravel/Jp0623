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
        $phone = HomePageData::site()['phone'];

        return [
            'title' => 'Contact us',
            'lead' => "Complete the form below or call {$phone}.",
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function panel(): array
    {
        $phone = HomePageData::site()['phone'];

        return [
            'title' => 'Talk to our team',
            'phone' => $phone,
            'phoneHref' => 'tel:+18000000000',
            'email' => 'hello@vendingmachinelocationsforsale.com',
            'emailHref' => 'mailto:hello@vendingmachinelocationsforsale.com',
            'hours' => 'Mon–Fri, 8am–6pm ET',
            'operatorTitle' => 'Vending operator?',
            'operatorLinks' => [
                [
                    'label' => 'operator page',
                    'href' => '#',
                ],
                [
                    'label' => 'log in',
                    'href' => '#',
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
