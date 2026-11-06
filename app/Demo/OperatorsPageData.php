<?php

namespace App\Demo;

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
        return [
            'eyebrow' => 'For vending operators',
            'title' => 'Join the largest network of independent vending operators.',
            'lead' => 'See verified location leads in your area: businesses that asked for vending and confirmed their details with our team.',
            'primaryCta' => [
                'label' => 'Get my free trial',
                'href' => '/register',
            ],
            'secondaryCta' => [
                'label' => 'Operator login',
                'href' => '/login',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pricing(): array
    {
        return [
            'eyebrow' => 'Operator membership',
            'price' => '$39',
            'period' => '/ month',
            'text' => '30 days free, then $39/month. No commitment. Cancel anytime.',
            'cta' => [
                'label' => 'Start free trial',
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
                [
                    'title' => '30% referral commission',
                    'text' => "Submit leads you can't service and earn when they sell.",
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function faqs(): array
    {
        return [
            'eyebrow' => 'FAQ',
            'title' => 'Operator questions.',
            'items' => [
                [
                    'q' => "How much does it cost and what's included?",
                    'a' => '$39 per month after a 30-day free trial. You get access to verified location leads in your area and alerts when new ones are added.',
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
