<?php

use App\Services\Payments\SquareWebhookVerifier;

beforeEach(function () {
    config()->set('services.square.webhook_signature_key', 'test-signature-key');
    config()->set('services.square.webhook_url', 'https://example.test/webhooks/square');
});

test('a correctly signed body is valid', function () {
    $body = '{"type":"payment.updated","event_id":"evt-1"}';
    $expected = base64_encode(hash_hmac('sha256', 'https://example.test/webhooks/square'.$body, 'test-signature-key', true));

    expect(app(SquareWebhookVerifier::class)->isValid($body, $expected))->toBeTrue();
});

test('a tampered body, wrong url or missing header is rejected', function () {
    $verifier = app(SquareWebhookVerifier::class);
    $body = '{"type":"payment.updated"}';
    $signature = $verifier->signatureFor($body);

    expect($verifier->isValid($body.' ', $signature))->toBeFalse()
        ->and($verifier->isValid($body, null))->toBeFalse()
        ->and($verifier->isValid($body, ''))->toBeFalse()
        ->and($verifier->isValid($body, 'not-the-signature'))->toBeFalse();

    config()->set('services.square.webhook_url', 'https://other.test/webhooks/square');

    expect($verifier->isValid($body, $signature))->toBeFalse();
});

test('verification fails closed when no signature key is configured', function () {
    config()->set('services.square.webhook_signature_key', '');

    $verifier = app(SquareWebhookVerifier::class);

    expect($verifier->isValid('{}', $verifier->signatureFor('{}')))->toBeFalse();
});

test('the notification url falls back to the webhook route path', function () {
    config()->set('services.square.webhook_url', null);

    expect(app(SquareWebhookVerifier::class)->notificationUrl())->toEndWith('/webhooks/square');
});
