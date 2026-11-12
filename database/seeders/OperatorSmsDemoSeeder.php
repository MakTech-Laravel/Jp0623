<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\OperatorProfile;
use App\Models\User;
use App\Models\VendingRequest;
use App\Services\NearbyOperatorLocator;
use App\Services\PhotonLocationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OperatorSmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $photon = app(PhotonLocationService::class);
        $locator = app(NearbyOperatorLocator::class);

        $houston = $this->firstSuggestion($photon, 'Houston, TX');

        $lead = VendingRequest::query()->updateOrCreate(
            ['email' => 'demo-lead@vmls.test'],
            [
                'location_type' => 'Business / office',
                'business_name' => 'Demo Houston HQ',
                'address_or_zip' => $houston['label'],
                'address' => $houston['address'],
                'city' => $houston['city'] !== '' ? $houston['city'] : 'Houston',
                'state' => $houston['state'] !== '' ? $houston['state'] : 'TX',
                'postal_code' => $houston['postal_code'],
                'latitude' => $houston['latitude'],
                'longitude' => $houston['longitude'],
                'employees_on_site' => 50,
                'daily_visitors' => 100,
                'placement' => 'Inside',
                'space_notes' => 'Photon-geocoded demo lead for Operator SMS testing',
                'food_on_site' => 'No',
                'comparing_quotes' => 'No',
                'machine_interests' => ['Snack', 'Drinks'],
                'machine_count' => '2',
                'existing_vending' => 'No',
                'switch_reason' => null,
                'contact_name' => 'Demo Contact',
                'job_title' => 'Facilities',
                'phone' => '(713) 555-0001',
                'terms_accepted' => true,
                'status' => VendingRequest::STATUS_NEW,
            ],
        );

        $operators = [
            [
                'email' => 'near.operator@vmls.test',
                'first_name' => 'Near',
                'last_name' => 'Houston',
                'company_name' => 'Near Mile Vending',
                'phone' => '(713) 555-1001',
                'query' => 'Houston, TX',
            ],
            [
                'email' => 'fifty.operator@vmls.test',
                'first_name' => 'Fifty',
                'last_name' => 'Miles',
                'company_name' => 'Fifty Mile Vending',
                'phone' => '(979) 555-1002',
                'query' => 'College Station, TX',
            ],
            [
                'email' => 'band.operator@vmls.test',
                'first_name' => 'Band',
                'last_name' => 'Hundred',
                'company_name' => '100-200 Mile Vending',
                'phone' => '(210) 555-1003',
                'query' => 'San Antonio, TX',
            ],
            [
                'email' => 'far.operator@vmls.test',
                'first_name' => 'Far',
                'last_name' => 'Dallas',
                'company_name' => '200-300 Mile Vending',
                'phone' => '(214) 555-1004',
                'query' => 'Dallas, TX',
            ],
            [
                'email' => 'plus.operator@vmls.test',
                'first_name' => 'Plus',
                'last_name' => 'Albuquerque',
                'company_name' => '300 Plus Vending',
                'phone' => '(505) 555-1005',
                'query' => 'Albuquerque, NM',
            ],
        ];

        foreach ($operators as $data) {
            $place = $this->firstSuggestion($photon, $data['query']);
            $miles = round($locator->distanceMiles(
                (float) $lead->latitude,
                (float) $lead->longitude,
                $place['latitude'],
                $place['longitude'],
            ), 1);

            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'].' '.$data['last_name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->hasRole(RoleEnum::USER->value)) {
                $user->assignRole(RoleEnum::USER->value);
            }

            OperatorProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'company_name' => $data['company_name'],
                    'phone' => $data['phone'],
                    'owns_machines' => true,
                    'machine_types' => ['Snack', 'Drinks'],
                    'machines_needed' => '2',
                    'primary_city' => trim($place['city'].', '.$place['state'], ', '),
                    'address' => $place['address'],
                    'city' => $place['city'] !== '' ? $place['city'] : explode(',', $data['query'])[0],
                    'state' => $place['state'] !== '' ? $place['state'] : 'TX',
                    'postal_code' => $place['postal_code'],
                    'latitude' => $place['latitude'],
                    'longitude' => $place['longitude'],
                    'terms_accepted_at' => now(),
                    'email_code_confirmed_at' => now(),
                    'trial_started_at' => now(),
                ],
            );

            $this->command?->info(sprintf(
                '  %s @ %s => %.1f miles from Houston lead',
                $data['email'],
                $place['label'],
                $miles,
            ));
        }

        $this->command?->info("Demo lead #{$lead->id} seeded from Photon ({$houston['label']}).");
    }

    /**
     * @return array{
     *     label: string,
     *     address: string|null,
     *     city: string,
     *     state: string,
     *     postal_code: string|null,
     *     latitude: float,
     *     longitude: float
     * }
     */
    private function firstSuggestion(PhotonLocationService $photon, string $query): array
    {
        $suggestions = $photon->suggest($query, 1);

        if ($suggestions === []) {
            throw new RuntimeException("Photon returned no results for [{$query}].");
        }

        return $suggestions[0];
    }
}
