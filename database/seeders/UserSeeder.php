<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\OperatorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        foreach ($this->users() as $user) {
            $name = $user['name'];
            $email = $user['email'];
            $password = $user['password'];
            $role = $user['role'] ?? null;

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );

            if ($role && RoleEnum::from($role)) {
                $user->syncRoles([$role]);
            }

            if ($role === RoleEnum::USER->value) {
                $this->seedOperatorProfile($user);
            }

            $created++;
        }

        $this->command->info("Users: {$created} created.");
    }

    /**
     * Give the demo operator a saved location so lead distances can be shown.
     */
    private function seedOperatorProfile(User $user): void
    {
        OperatorProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => 'Demo',
                'last_name' => 'Operator',
                'company_name' => 'Demo Vending Co',
                'phone' => '(555) 010-0000',
                'owns_machines' => true,
                'machine_types' => ['Combo', 'Snack'],
                'machines_needed' => '1',
                'primary_city' => 'Minneapolis, Minnesota',
                'address' => null,
                'city' => 'Minneapolis',
                'state' => 'Minnesota',
                'postal_code' => '55401',
                'latitude' => 44.9778,
                'longitude' => -93.2650,
                'terms_accepted_at' => now(),
                'email_code_confirmed_at' => now(),
            ],
        );
    }

    private function users(): array
    {
        return [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@dev.com',
                'password' => 'superadmin@dev.com',
                'role' => RoleEnum::SUPER_ADMIN->value,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@dev.com',
                'password' => 'admin@dev.com',
                'role' => RoleEnum::ADMIN->value,
            ],
            [
                'name' => 'Editor',
                'email' => 'editor@dev.com',
                'password' => 'editor@dev.com',
                'role' => RoleEnum::EDITOR->value,
            ],
            [
                'name' => 'Author',
                'email' => 'author@dev.com',
                'password' => 'author@dev.com',
                'role' => RoleEnum::AUTHOR->value,
            ],
            [
                'name' => 'Viewer',
                'email' => 'viewer@dev.com',
                'password' => 'viewer@dev.com',
                'role' => RoleEnum::VIEWER->value,
            ],
            [
                'name' => 'User',
                'email' => 'user@dev.com',
                'password' => 'user@dev.com',
                'role' => RoleEnum::USER->value,
            ],
        ];
    }
}
