<?php

use Illuminate\Support\Facades\Http;

test('location suggest requires at least three characters', function () {
    $this->getJson(route('register.location-suggest', ['q' => 'ho']))
        ->assertUnprocessable()
        ->assertJsonPath('suggestions', []);
});

test('location suggest returns mapped us suggestions from photon', function () {
    Http::fake([
        '*' => Http::response([
            'features' => [
                [
                    'geometry' => [
                        'coordinates' => [-95.3698, 29.7604],
                    ],
                    'properties' => [
                        'name' => 'Houston',
                        'city' => 'Houston',
                        'state' => 'Texas',
                        'postcode' => '77002',
                        'type' => 'city',
                        'countrycode' => 'US',
                    ],
                ],
                [
                    'geometry' => [
                        'coordinates' => [-95.37, 29.76],
                    ],
                    'properties' => [
                        'street' => 'Main St',
                        'housenumber' => '100',
                        'city' => 'Houston',
                        'state' => 'TX',
                        'postcode' => '77002',
                        'type' => 'house',
                    ],
                ],
                [
                    'geometry' => [
                        'coordinates' => [-0.1276, 51.5072],
                    ],
                    'properties' => [
                        'city' => 'London',
                        'state' => null,
                        'type' => 'city',
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->getJson(route('register.location-suggest', ['q' => 'houston']))
        ->assertOk()
        ->assertJsonCount(2, 'suggestions')
        ->assertJsonPath('suggestions.0.city', 'Houston')
        ->assertJsonPath('suggestions.0.state', 'Texas')
        ->assertJsonPath('suggestions.0.latitude', 29.7604)
        ->assertJsonPath('suggestions.0.longitude', -95.3698)
        ->assertJsonPath('suggestions.1.address', '100 Main St');
});

test('location suggest returns service unavailable when photon fails', function () {
    Http::fake([
        '*' => Http::response('error', 500),
    ]);

    $this->getJson(route('register.location-suggest', ['q' => 'houston']))
        ->assertStatus(503)
        ->assertJsonPath('suggestions', []);
});
