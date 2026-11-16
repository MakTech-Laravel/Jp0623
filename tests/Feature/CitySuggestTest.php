<?php

use App\Services\PhotonLocationService;
use App\Support\UsStates;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.photon.url' => 'https://photon.test/api']);
});

function photonCity(string $name, string $state, string $value = 'city', float $lon = -95.36, float $lat = 29.76): array
{
    return [
        'geometry' => ['coordinates' => [$lon, $lat]],
        'properties' => [
            'name' => $name,
            'state' => $state,
            'osm_key' => 'place',
            'osm_value' => $value,
            'county' => 'Some County',
        ],
    ];
}

test('state names and codes resolve to two letter codes', function () {
    expect(UsStates::code('Texas'))->toBe('TX')
        ->and(UsStates::code('new york'))->toBe('NY')
        ->and(UsStates::code('tx'))->toBe('TX')
        ->and(UsStates::code('Narnia'))->toBeNull()
        ->and(UsStates::code(null))->toBeNull();
});

test('city suggestions keep only places with a known state and are deduplicated', function () {
    Http::fake([
        'photon.test/*' => Http::response(['features' => [
            photonCity('Houston', 'Texas'),
            photonCity('Houston', 'Texas', 'town'),
            photonCity('Houstonia', 'Missouri', 'village'),
            photonCity('Houston', 'Narnia'),
            photonCity('Houston Street', 'Texas', 'road'),
        ]]),
    ]);

    $suggestions = app(PhotonLocationService::class)->suggestCities('hou');

    expect(collect($suggestions)->pluck('label')->all())->toBe(['Houston, TX', 'Houstonia, MO'])
        ->and($suggestions[0]['city'])->toBe('Houston')
        ->and($suggestions[0]['state'])->toBe('TX')
        ->and($suggestions[0]['latitude'])->toBe(29.76)
        ->and($suggestions[0]['longitude'])->toBe(-95.36);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'osm_tag=place:city')
        && str_contains($request->url(), 'osm_tag=place:town')
        && str_contains($request->url(), 'osm_tag=place:village')
        && str_contains($request->url(), 'countrycode=us'));
});

test('the suggest endpoint returns cities when type=city', function () {
    Http::fake(['photon.test/*' => Http::response(['features' => [photonCity('Austin', 'Texas')]])]);

    $this->getJson(route('register.location-suggest', ['q' => 'aus', 'type' => 'city']))
        ->assertOk()
        ->assertJsonPath('suggestions.0.label', 'Austin, TX');
});

test('the suggest endpoint requires three characters and a valid type', function () {
    $this->getJson(route('register.location-suggest', ['q' => 'au', 'type' => 'city']))
        ->assertStatus(422);

    $this->getJson(route('register.location-suggest', ['q' => 'austin', 'type' => 'bogus']))
        ->assertStatus(422);
});

test('the suggest endpoint reports an unavailable service', function () {
    Http::fake(['photon.test/*' => Http::response([], 500)]);

    $this->getJson(route('register.location-suggest', ['q' => 'austin', 'type' => 'city']))
        ->assertStatus(503)
        ->assertJsonPath('suggestions', []);
});
