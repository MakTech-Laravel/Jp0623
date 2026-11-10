<?php

use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\MachineFaq;
use Database\Seeders\MachineFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('machines page renders with database lineup', function () {
    $this->seed(MachineFaqSeeder::class);

    $category = MachineCategory::factory()->create([
        'name' => 'Combo',
        'sort_order' => 1,
    ]);

    Machine::factory()->create([
        'machine_category_id' => $category->id,
        'name' => 'Combo Machine',
        'slug' => 'combo',
        'sort_order' => 1,
    ]);

    Machine::factory()->create([
        'machine_category_id' => $category->id,
        'name' => 'Snack Machine',
        'slug' => 'snack',
        'sort_order' => 2,
    ]);

    Machine::factory()->inactive()->create([
        'machine_category_id' => $category->id,
        'slug' => 'hidden-machine',
    ]);

    $this->get(route('machines'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/machines')
            ->has('site')
            ->has('hero')
            ->where(
                'lineup.text',
                'Availability of machines and or micro markets may vary by region. Multiple machines can be placed at locations with more visitors/employees.',
            )
            ->where('lineup.filters', ['All', 'Combo'])
            ->has('lineup.items', 2)
            ->where('lineup.items.0.href', '/machines/combo')
            ->where('lineup.items.0.id', 'combo')
            ->where('lineup.items.0.type', 'Combo')
            ->has('tech.items', 4)
            ->has('industries.items', 10)
            ->has('faqs.items', 4)
            ->where('faqs.items.0.q', 'Are the machines really free?')
        );
});

test('machines page faqs come from the database and hide inactive items', function () {
    MachineFaq::factory()->create([
        'question' => 'Active machine FAQ?',
        'answer' => 'Yes.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    MachineFaq::factory()->inactive()->create([
        'question' => 'Hidden machine FAQ?',
        'answer' => 'No.',
        'sort_order' => 2,
    ]);

    $this->get(route('machines'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('faqs.items', 1)
            ->where('faqs.items.0.q', 'Active machine FAQ?')
            ->where('faqs.items.0.a', 'Yes.')
        );
});

test('machines page lists by sort order then id so newer equal sorts appear later', function () {
    $category = MachineCategory::factory()->create();

    Machine::factory()->create([
        'machine_category_id' => $category->id,
        'name' => 'First',
        'slug' => 'first',
        'sort_order' => 1,
    ]);

    Machine::factory()->create([
        'machine_category_id' => $category->id,
        'name' => 'Second',
        'slug' => 'second',
        'sort_order' => 1,
    ]);

    $this->get(route('machines'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('lineup.items.0.id', 'first')
            ->where('lineup.items.1.id', 'second')
        );
});
