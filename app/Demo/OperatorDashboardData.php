<?php

namespace App\Demo;

use App\Models\User;
use App\Services\LeadService;

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

        $leadService = app(LeadService::class);
        $leads = $leadService->availableFor($user);
        $counts = $leadService->counts($user);

        $activity = array_map(
            fn (array $lead): array => [
                'text' => "New lead listed: {$lead['type']}".($lead['city'] !== '' ? " in {$lead['city']}" : ''),
                'when' => $lead['listed'],
            ],
            array_slice($leads, 0, 3),
        );

        return [
            'firstName' => $firstName,
            'primaryCity' => $profile?->primary_city ?: 'Not set',
            'subscription' => [
                'status' => 'free',
                'label' => 'Free',
            ],
            'kpis' => [
                [
                    'label' => 'Available leads',
                    'value' => (string) $counts['available'],
                    'hint' => null,
                ],
                [
                    'label' => 'Purchased leads',
                    'value' => (string) $counts['purchased'],
                    'hint' => null,
                ],
                [
                    'label' => 'Membership',
                    'value' => 'Free',
                    'hint' => null,
                ],
            ],
            'leads' => $leads,
            'newestLeads' => array_slice($leads, 0, 4),
            'activity' => $activity,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function subscription(): array
    {
        return [
            'planPrice' => 'Free',
            'status' => 'free',
            'includes' => [
                'All verified leads in your area',
                'SMS and email alerts',
                'Lead issue support',
                'Proposal builder',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function tickets(): array
    {
        return [
            [
                'id' => 'T-208',
                'lead' => 'Peach Bank · L-3074',
                'issue' => 'Could not reach the location',
                'opened' => 'Oct 30',
                'status' => 'Under review',
                'outcome' => null,
            ],
            [
                'id' => 'T-171',
                'lead' => 'Lone Star Gym · L-2950',
                'issue' => 'Phone number is wrong',
                'opened' => 'Sep 14',
                'status' => 'Resolved',
                'outcome' => '$40 account credit',
            ],
            [
                'id' => 'T-133',
                'lead' => 'Bayou Clinic · L-2811',
                'issue' => 'Ordered by mistake',
                'opened' => 'Aug 02',
                'status' => 'Refunded',
                'outcome' => '$95 refund to card',
            ],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function faq(): array
    {
        return [
            [
                'question' => 'What is a qualified location lead?',
                'answer' => 'A location that asked for vending, spoke with our team and passed verification. You get the details they gave us plus private notes.',
            ],
            [
                'question' => 'What am I purchasing?',
                'answer' => "The location's contact information and the chance to win their business. It is not a guaranteed contract, placement or sale.",
            ],
            [
                'question' => 'What happens after I buy a lead?',
                'answer' => 'The contact details and notes unlock right away. We tell the location an operator will reach out.',
            ],
            [
                'question' => 'Can I get a refund?',
                'answer' => 'Report an issue within 7 days from Purchased location leads. We verify it and may issue a credit or refund.',
            ],
            [
                'question' => 'How do I change my lead area?',
                'answer' => 'Update your primary city in Location settings. You can change it 5 times every 90 days.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function routes(): array
    {
        return [
            [
                'id' => 'R-58',
                'city' => 'Dallas–Fort Worth, TX',
                'about' => 'Office & light industrial',
                'locs' => 14,
                'machines' => 22,
                'rev' => 168000,
                'ask' => 210000,
                'fin' => 'Yes',
                'listed' => 'Oct 25',
            ],
            [
                'id' => 'R-55',
                'city' => 'Charlotte, NC',
                'about' => 'Healthcare combo route',
                'locs' => 9,
                'machines' => 13,
                'rev' => 96500,
                'ask' => 118000,
                'fin' => 'No',
                'listed' => 'Oct 19',
            ],
            [
                'id' => 'R-52',
                'city' => 'Orlando, FL',
                'about' => 'Apartment AI coolers',
                'locs' => 6,
                'machines' => 6,
                'rev' => 54000,
                'ask' => 72000,
                'fin' => 'Yes',
                'listed' => 'Oct 11',
            ],
            [
                'id' => 'R-49',
                'city' => 'Indianapolis, IN',
                'about' => 'School & gym snack route',
                'locs' => 11,
                'machines' => 17,
                'rev' => 81200,
                'ask' => 95000,
                'fin' => 'Yes',
                'listed' => 'Oct 03',
            ],
            [
                'id' => 'R-44',
                'city' => 'Phoenix, AZ',
                'about' => 'Warehouse drink route',
                'locs' => 8,
                'machines' => 12,
                'rev' => 72500,
                'ask' => 84000,
                'fin' => 'No',
                'listed' => 'Sep 21',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function account(User $user): array
    {
        $profile = $user->operatorProfile;

        return [
            'firstName' => $profile?->first_name ?: str($user->name)->before(' ')->toString() ?: 'Alex',
            'lastName' => $profile?->last_name ?: str($user->name)->after(' ')->toString() ?: 'Jordan',
            'company' => $profile?->company_name ?: 'Gulf Coast Vending',
            'email' => $user->email,
            'phone' => $profile?->phone ?: '(555) 010-0000',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function locationSettings(User $user): array
    {
        return [
            'primaryCity' => $user->operatorProfile?->primary_city ?: 'Houston, TX',
            'changesRemaining' => 5,
            'changesTotal' => 5,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function billing(): array
    {
        return [
            'paymentMethod' => [
                'brand' => 'Visa',
                'last4' => '0000',
                'expires' => '08/28',
            ],
            'invoices' => [
                [
                    'id' => 'INV-1042',
                    'location' => 'Gulf Freight Co.',
                    'date' => 'Oct 28',
                    'status' => 'Paid',
                    'description' => 'Location lead',
                    'amount' => 185.0,
                    'qty' => 1,
                ],
                [
                    'id' => 'INV-1036',
                    'location' => 'Peach Bank',
                    'date' => 'Oct 21',
                    'status' => 'Paid',
                    'description' => 'Location lead ($40 credit used)',
                    'amount' => 110.0,
                    'qty' => 1,
                ],
            ],
        ];
    }
}
