<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaymentEvent;
use App\Services\Payments\SquareWebhookHandler;
use App\Services\Payments\SquareWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives Square webhooks (payment.updated etc.).
 *
 * The signature is checked against the RAW request body. Every delivery is
 * logged once by `event_id`; a delivery that failed midway is processed again on
 * Square's retry because `processed_at` is only set after the handler succeeds.
 */
class SquareWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        SquareWebhookVerifier $verifier,
        SquareWebhookHandler $handler,
    ): Response {
        $rawBody = $request->getContent();

        if (! $verifier->isValid($rawBody, $request->header(SquareWebhookVerifier::SIGNATURE_HEADER))) {
            Log::warning('square.webhook.invalid_signature', ['ip' => $request->ip()]);

            return response('Invalid signature.', 403);
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload) || ! is_string($payload['event_id'] ?? null) || ! is_string($payload['type'] ?? null)) {
            return response('Invalid payload.', 400);
        }

        $event = PaymentEvent::query()->createOrFirst(
            ['event_id' => $payload['event_id']],
            ['provider' => 'square', 'type' => $payload['type'], 'payload' => $payload],
        );

        if ($event->processed_at !== null) {
            return response('Already processed.', 200);
        }

        $outcome = $handler->handle($event);

        $event->forceFill(['outcome' => $outcome, 'processed_at' => now()])->save();

        if ($outcome === PaymentEvent::OUTCOME_MISMATCH) {
            Log::critical('square.webhook.payment_mismatch', ['event_id' => $event->event_id]);
        }

        return response('OK', 200);
    }
}
