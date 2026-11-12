<?php

use App\Enums\RoleEnum;
use App\Enums\SmsTemplatePurpose;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Support\SmsTemplateRenderer;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('admin can view sms templates page with instructions', function () {
    SmsTemplate::factory()->create([
        'name' => 'Manual A',
        'purpose' => SmsTemplatePurpose::MANUAL,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.operator-sms.templates.index'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/operator-sms/templates')
                ->has('templates', 1)
                ->has('instructions')
                ->has('variables')
                ->has('purposes', 3)
        );
});

test('admin can create an auto vending template and it deactivates the previous one', function () {
    $old = SmsTemplate::factory()->autoVending()->create([
        'name' => 'Old auto vending',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.operator-sms.templates.store'), [
            'name' => 'New auto vending',
            'purpose' => SmsTemplatePurpose::AUTO_VENDING->value,
            'body' => 'New vending lead: {{business_name}} at {{location}}. Call {{phone}}.',
            'is_active' => true,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.operator-sms.templates.index'));

    expect($old->fresh()->is_active)->toBeFalse()
        ->and(SmsTemplate::query()->where('purpose', SmsTemplatePurpose::AUTO_VENDING)->where('is_active', true)->count())->toBe(1)
        ->and(SmsTemplate::query()->where('name', 'New auto vending')->exists())->toBeTrue();
});

test('admin can create multiple manual templates', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.operator-sms.templates.store'), [
            'name' => 'Manual one',
            'purpose' => SmsTemplatePurpose::MANUAL->value,
            'body' => 'Opportunity nearby. Check your dashboard.',
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->actingAs($this->admin)
        ->post(route('admin.operator-sms.templates.store'), [
            'name' => 'Manual two',
            'purpose' => SmsTemplatePurpose::MANUAL->value,
            'body' => 'Follow up on the nearby lead today.',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(SmsTemplate::query()->where('purpose', SmsTemplatePurpose::MANUAL)->count())->toBe(2);
});

test('sms template renderer replaces placeholders', function () {
    $message = SmsTemplateRenderer::render(
        'Lead {{business_name}} in {{location}} — call {{phone}}.',
        [
            'business_name' => 'Harbor Offices',
            'location' => 'Houston, TX',
            'phone' => '(555) 123-4567',
        ],
    );

    expect($message)->toBe('Lead Harbor Offices in Houston, TX — call (555) 123-4567.');
});

test('send sms page includes manual templates', function () {
    SmsTemplate::factory()->create([
        'name' => 'Manual ready',
        'purpose' => SmsTemplatePurpose::MANUAL,
        'body' => 'Ready message',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.operator-sms.index'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/operator-sms/index')
                ->has('manualTemplates', 1)
                ->where('manualTemplates.0.name', 'Manual ready')
        );
});
