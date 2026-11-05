<?php

namespace App\Demo;

use App\Models\User;

class OperatorDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(User $user): array
    {
        $profile = $user->operatorProfile;
        $firstName = $profile?->first_name
            ?: str($user->name)->before(' ')->toString()
            ?: $user->name;

        $leads = self::leads();

        return [
            'firstName' => $firstName,
            'primaryCity' => $profile?->primary_city ?: 'Houston, TX',
            'subscription' => [
                'status' => 'trial',
                'daysLeft' => 9,
                'label' => 'Trial',
            ],
            'kpis' => [
                [
                    'label' => 'Leads near you',
                    'value' => (string) count($leads),
                    'hint' => '+3 this week',
                ],
                [
                    'label' => 'Purchased leads',
                    'value' => '2',
                    'hint' => null,
                ],
                [
                    'label' => 'Commission earned',
                    'value' => '$84',
                    'hint' => '$42 pending',
                ],
                [
                    'label' => 'Subscription',
                    'value' => 'Trial',
                    'hint' => null,
                ],
            ],
            'leads' => $leads,
            'newestLeads' => array_slice($leads, 0, 4),
            'activity' => [
                [
                    'text' => 'New lead listed: Cold-storage warehouse',
                    'when' => '12 min ago',
                ],
                [
                    'text' => 'Your lead L-3074 issue is under review',
                    'when' => 'Yesterday',
                ],
                [
                    'text' => 'Commission $42 approved',
                    'when' => 'Oct 28',
                ],
            ],
            'trialBanner' => [
                'message' => 'Free trial: 9 days left. Subscribe to keep buying leads after Dec 1.',
                'ctaLabel' => 'Manage subscription',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function leads(): array
    {
        return [
            [
                'id' => 'L-3108',
                'type' => 'Cold-storage warehouse',
                'cat' => 'Warehouse',
                'city' => 'Houston',
                'st' => 'TX',
                'zip' => '77029',
                'dist' => 12,
                'people' => 140,
                'wants' => 'Combo + drinks',
                'io' => 'Inside',
                'price' => 185,
                'listed' => 'Today',
                'hours' => '24/7, 3 shifts',
                'contact' => 'Dana Ruiz · Facilities Manager · (555) 010-4471',
            ],
            [
                'id' => 'L-3104',
                'type' => 'Regional bank office',
                'cat' => 'Office',
                'city' => 'Pasadena',
                'st' => 'TX',
                'zip' => '77506',
                'dist' => 18,
                'people' => 120,
                'wants' => 'Combo + coffee',
                'io' => 'Inside',
                'price' => 150,
                'listed' => 'Yesterday',
                'hours' => 'Mon–Fri',
                'contact' => 'Sam Patel · Office Manager · (555) 010-9932',
            ],
            [
                'id' => 'L-3101',
                'type' => 'Boutique hotel',
                'cat' => 'Hotel',
                'city' => 'Sugar Land',
                'st' => 'TX',
                'zip' => '77478',
                'dist' => 24,
                'people' => 96,
                'wants' => 'AI cooler',
                'io' => 'Inside',
                'price' => 130,
                'listed' => 'Oct 29',
                'hours' => '24/7',
                'contact' => 'Lee Chen · GM · (555) 010-2210',
            ],
            [
                'id' => 'L-3097',
                'type' => 'Plastics manufacturer',
                'cat' => 'Manufacturing',
                'city' => 'Baytown',
                'st' => 'TX',
                'zip' => '77520',
                'dist' => 31,
                'people' => 210,
                'wants' => 'Micro market',
                'io' => 'Inside',
                'price' => 240,
                'listed' => 'Oct 28',
                'hours' => '2 shifts',
                'contact' => 'Pat Moore · HR Lead · (555) 010-8812',
            ],
            [
                'id' => 'L-3093',
                'type' => '24-hour gym',
                'cat' => 'Gym',
                'city' => 'Katy',
                'st' => 'TX',
                'zip' => '77494',
                'dist' => 29,
                'people' => 900,
                'wants' => 'Drinks + protein',
                'io' => 'Inside',
                'price' => 120,
                'listed' => 'Oct 27',
                'hours' => '24/7',
                'contact' => 'Jo Rivera · Owner · (555) 010-3390',
            ],
            [
                'id' => 'L-3090',
                'type' => 'Apartment complex',
                'cat' => 'Apartment',
                'city' => 'Spring',
                'st' => 'TX',
                'zip' => '77373',
                'dist' => 22,
                'people' => 312,
                'wants' => 'AI cooler',
                'io' => 'Inside',
                'price' => 160,
                'listed' => 'Oct 26',
                'hours' => '24/7',
                'contact' => 'Kim Ali · Property Manager · (555) 010-6677',
            ],
            [
                'id' => 'L-3086',
                'type' => 'Car dealership',
                'cat' => 'Retail',
                'city' => 'Conroe',
                'st' => 'TX',
                'zip' => '77301',
                'dist' => 41,
                'people' => 60,
                'wants' => 'Combo',
                'io' => 'Inside',
                'price' => 110,
                'listed' => 'Oct 24',
                'hours' => 'Mon–Sat',
                'contact' => 'Chris Diaz · Sales Manager · (555) 010-4455',
            ],
            [
                'id' => 'L-3081',
                'type' => 'Medical office park',
                'cat' => 'Medical',
                'city' => 'Galveston',
                'st' => 'TX',
                'zip' => '77550',
                'dist' => 48,
                'people' => 85,
                'wants' => 'Healthy combo',
                'io' => 'Outside (covered)',
                'price' => 140,
                'listed' => 'Oct 22',
                'hours' => 'Mon–Sat',
                'contact' => 'Ava Brooks · Practice Admin · (555) 010-7788',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function purchasedLeads(): array
    {
        return [
            [
                'id' => 'L-3099',
                'purchased_at' => 'Oct 28',
                'name' => 'Gulf Freight Co.',
                'type' => 'Warehouse',
                'city' => 'Houston',
                'st' => 'TX',
                'zip' => '77029',
                'address' => '1200 Example Rd',
                'contact_name' => 'Dana Ruiz',
                'contact_phone' => '(555) 010-4471',
                'price' => 185,
                'credit_used' => null,
                'status' => 'ok',
            ],
            [
                'id' => 'L-3074',
                'purchased_at' => 'Oct 21',
                'name' => 'Peach Bank',
                'type' => 'Office',
                'city' => 'Pasadena',
                'st' => 'TX',
                'zip' => '77506',
                'address' => '45 Sample St',
                'contact_name' => 'Sam Patel',
                'contact_phone' => '(555) 010-9932',
                'price' => 150,
                'credit_used' => 40,
                'status' => 'issue_open',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function subscription(): array
    {
        return [
            'planPrice' => '$39',
            'status' => 'trial',
            'daysLeft' => 9,
            'renews' => 'Dec 1',
            'includes' => [
                'All verified leads in your area',
                'SMS and email alerts',
                'Lead issue support',
                'Proposal builder',
                'Referral commissions',
            ],
        ];
    }
}
