<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            MachineCategorySeeder::class,
            MachineSeeder::class,
            ProductSeeder::class,
            HomeFaqSeeder::class,
            MachineFaqSeeder::class,
            OfficeCoffeeFaqSeeder::class,
            AboutFaqSeeder::class,
            CoffeeMachineSeeder::class,
            CoffeeTypeSeeder::class,
            LocationTypeSeeder::class,
            ArticleSeeder::class,
            CitySeeder::class,
            SmsTemplateSeeder::class,
            MembershipSettingSeeder::class,
            LegalPageSeeder::class,
        ]);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
