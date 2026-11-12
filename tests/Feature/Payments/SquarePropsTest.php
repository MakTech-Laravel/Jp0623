<?php

use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

test('operators receive only the public Square settings', function () {
    configureSquare();
    VendingRequest::factory()->create();

    $response = $this->actingAs(makeOperator())->get(route('leads.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('square.applicationId', 'sandbox-sq0idb-test')
        ->where('square.locationId', 'LOC123')
        ->where('square.sdkUrl', 'https://sandbox.web.squarecdn.com/v1/square.js')
        ->where('square.currency', 'USD')
    );

    $body = json_encode($response->viewData('page')['props']);

    expect($body)->not->toContain('test-access-token')
        ->and($body)->not->toContain('test-signature-key');
});

test('the Square prop is null when payments are not configured', function () {
    configureSquare(['location_id' => '']);

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->where('square', null));
});

test('guests never get Square settings', function () {
    configureSquare();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('square', null));
});
