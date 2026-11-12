<?php

use App\Enums\RoleEnum;
use App\Models\LeadPurchase;
use App\Models\OperatorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Create a verified operator (role `user`) with an operator profile.
 *
 * @param  array<string, mixed>  $profile
 */
function makeOperator(array $profile = []): User
{
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);
    OperatorProfile::factory()->create(['user_id' => $user->id] + $profile);

    return $user;
}

/**
 * Point the app at a fully configured (fake) Square sandbox account.
 *
 * @param  array<string, mixed>  $overrides
 */
function configureSquare(array $overrides = []): void
{
    config()->set('services.square', array_replace([
        'environment' => 'sandbox',
        'application_id' => 'sandbox-sq0idb-test',
        'access_token' => 'test-access-token',
        'location_id' => 'LOC123',
        'webhook_signature_key' => 'test-signature-key',
        'webhook_url' => 'https://example.test/webhooks/square',
        'currency' => 'USD',
        'api_version' => '2026-09-16',
        'base_url' => 'https://connect.squareupsandbox.com',
        'sdk_url' => 'https://sandbox.web.squarecdn.com/v1/square.js',
    ], $overrides));
}

/**
 * A Square CreatePayment / webhook `payment` object for the given purchase.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function squarePaymentObject(?LeadPurchase $purchase = null, array $overrides = []): array
{
    return array_replace_recursive([
        'id' => 'PAY-'.($purchase?->id ?? 1),
        'status' => 'COMPLETED',
        'amount_money' => [
            'amount' => $purchase?->amount_cents ?? 15000,
            'currency' => 'USD',
        ],
        'location_id' => 'LOC123',
        'reference_id' => 'LP-'.($purchase?->id ?? 1),
        'order_id' => 'ORDER-1',
        'receipt_url' => 'https://squareup.com/receipt/preview/abc',
        'receipt_number' => 'abc1',
        'card_details' => [
            'status' => 'CAPTURED',
            'card' => ['card_brand' => 'VISA', 'last_4' => '1111', 'fingerprint' => 'sq-fp'],
        ],
    ], $overrides);
}

/**
 * Http::fake() for Square calls that also lets Inertia's dev SSR probe fail
 * quietly (it is blocked by Http::preventStrayRequests()).
 *
 * @param  array<string, mixed>  $stubs
 */
function fakeSquareHttp(array $stubs): void
{
    Http::fake($stubs + ['*__inertia_ssr*' => Http::response('', 404)]);
}

/**
 * How many requests were sent to Square's API (ignores Inertia's SSR probe).
 */
function squareCallCount(): int
{
    return Http::recorded(
        fn (Request $request): bool => str_contains($request->url(), 'squareupsandbox.com'),
    )->count();
}
