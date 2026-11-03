<?php

namespace App\Demo;

class AtmPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => HomePageData::site(),
            'hero' => self::hero(),
            'benefits' => self::benefits(),
            'goodFit' => self::goodFit(),
            'request' => self::request(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'title' => 'ATM machines',
            'lead' => 'Free ATM placement for businesses with cash-paying customers. We install, fill and service it.',
            'cta' => [
                'label' => 'Request an ATM',
                'href' => '#atm-form',
            ],
            'art' => 'atm',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function benefits(): array
    {
        return [
            'eyebrow' => 'Why add an ATM',
            'title' => 'Bring in more cash sales.',
            'items' => [
                ['title' => 'No cost', 'text' => 'Equipment, install and cash loading included.'],
                ['title' => 'Earn surcharge share', 'text' => 'Ask about revenue sharing on each withdrawal.'],
                ['title' => 'Secure', 'text' => 'Bolted, alarmed units with encrypted processing.'],
                ['title' => '24/7 support', 'text' => 'Local service if the ATM goes down.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function goodFit(): array
    {
        return [
            'eyebrow' => 'Good fit for',
            'title' => 'Locations that do well with an ATM.',
            'items' => [
                'Convenience stores',
                'Bars & restaurants',
                'Hotels',
                'Event venues',
                'Salons',
                'Laundromats',
                'Gas stations',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function request(): array
    {
        return [
            'title' => 'ATM service request',
            'text' => 'Tell us about your location and an ATM specialist will be in touch.',
            'successMessage' => 'Thanks! An ATM specialist will contact you shortly.',
            'submitLabel' => 'Request an ATM',
            'subjectLabel' => 'location',
        ];
    }
}
