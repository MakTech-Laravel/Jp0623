<?php

use App\Models\OperatorProfile;
use App\Services\NearbyOperatorLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('locator returns only operators within the radius with phones', function () {
    $near = OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(212) 555-0100',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    OperatorProfile::factory()->create([
        'phone' => null,
        'latitude' => 29.7610,
        'longitude' => -95.3700,
    ]);

    $matches = app(NearbyOperatorLocator::class)->withinMiles(29.7604, -95.3698, 100);

    expect($matches)->toHaveCount(1)
        ->and($matches->first()->id)->toBe($near->id);
});

test('haversine distance is roughly correct for houston to nearby suburb', function () {
    $miles = app(NearbyOperatorLocator::class)->distanceMiles(
        29.7604,
        -95.3698,
        29.7900,
        -95.4000,
    );

    expect($miles)->toBeGreaterThan(2)
        ->and($miles)->toBeLessThan(5);
});
