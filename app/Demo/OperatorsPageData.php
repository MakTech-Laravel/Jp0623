<?php

namespace App\Demo;

use App\Services\MembershipSettingsService;

class OperatorsPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'pricing' => self::pricing(),
            'steps' => self::steps(),
            'tools' => self::tools(),
            'faqs' => self::faqs(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        return HomePageData::site();
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        $settings = app(MembershipSettingsService::class)->current();
        $isPaid = $settings->isPaid();
        $price = (int) $settings->monthly_price;

        return [
            'eyebrow' => 'For vending operators',
            'title' => 'Join the largest network of independent vending operators.',
            'lead' => 'See verified location leads in your area: businesses that asked for vending and confirmed their details with our team.',
            'primaryCta' => [
                'label' => $isPaid ? 'Create account' : 'Join free',
                'href' => '/register',
            ],
            'secondaryCta' => [
                'label' => 'Operator login',
                'href' => '/login',
            ],
            'membership' => [
                'mode' => $settings->mode,
                'monthly_price' => $price,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pricing(): array
    {
        $settings = app(MembershipSettingsService::class)->current();
        $isPaid = $settings->isPaid();
        $price = (int) $settings->monthly_price;

        if ($isPaid) {
            return [
                'eyebrow' => 'Operator membership',
                'price' => '$'.$price,
                'period' => '/mo',
                'text' => "Membership is \${$price}/month and unlocks the lead marketplace. You still pay per lead when you buy one.",
                'cta' => [
                    'label' => 'Create account',
                    'href' => '/register',
                ],
            ];
        }

        return [
            'eyebrow' => 'Operator membership',
            'price' => 'Free',
            'period' => '',
            'text' => 'Free to join. No membership fees and no credit card required.',
            'cta' => [
                'label' => 'Join free',
                'href' => '/register',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function steps(): array
    {
        return [
            'eyebrow' => 'How it works',
            'title' => 'From a request to a lead in your inbox.',
            'items' => [
                [
                    'title' => 'A business requests vending',
                    'text' => 'They fill out a request on one of our sites.',
                ],
                [
                    'title' => 'We verify it',
                    'text' => 'Our team calls to confirm details and explain the program.',
                ],
                [
                    'title' => "It's listed for sale",
                    'text' => 'Operators nearby get an email or SMS alert.',
                ],
                [
                    'title' => 'First buyer gets it',
                    'text' => 'You contact the business and close the placement.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tools(): array
    {
        return [
            'eyebrow' => 'Tools',
            'title' => 'More than leads.',
            'items' => [
                [
                    'title' => 'Lead marketplace',
                    'text' => 'Browse and buy verified locations near you.',
                ],
                [
                    'title' => 'Proposal builder',
                    'text' => 'Send a professional proposal to large accounts.',
                ],
                [
                    'title' => 'Routes for sale',
                    'text' => 'Buy a route or get a valuation for yours.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function faqs(): array
    {
        $settings = app(MembershipSettingsService::class)->current();
        $isPaid = $settings->isPaid();
        $price = (int) $settings->monthly_price;

        $costAnswer = $isPaid
            ? "Membership is \${$price}/month and includes access to verified location leads in your area plus alerts when new ones are added. You also pay when you buy a lead."
            : 'Membership is free. You get access to verified location leads in your area and alerts when new ones are added.';

        return [
            'eyebrow' => 'FAQ',
            'title' => 'Operator questions.',
            'items' => [
                [
                    'q' => "How much does it cost and what's included?",
                    'a' => $costAnswer,
                ],
                [
                    'q' => 'What are qualified location leads?',
                    'a' => 'Businesses that requested vending on our sites and confirmed their information with our team by phone.',
                ],
                [
                    'q' => 'How do I get these locations?',
                    'a' => 'Buy the listing in the operator portal. The first operator to buy a lead gets it.',
                ],
                [
                    'q' => 'How will I see leads in my area?',
                    'a' => 'We email and text you when a new lead is listed within your alert radius.',
                ],
            ],
        ];
    }
}
