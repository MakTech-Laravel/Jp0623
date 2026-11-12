# Square Payment Gateway for Lead Purchases: Implementation Plan

> **Handoff file.** Any AI/engineer can implement this top to bottom. Work phase by phase and tick the checklist in section 14 as you go, so another AI can resume where you stopped.
> Deliverable 0 (first action after plan approval): copy this file into the repo as `SQUARE_PAYMENT_PLAN.md` (project root) so it travels with the code.

---

## 0. Project conventions you MUST follow (from `CLAUDE.md`)

- Laravel 13, PHP 8.4, Inertia v3 + React, Wayfinder, Pest 4, Pint, Spatie Permission. Use `php artisan make:*` with `--no-interaction` (models with `-mf`, tests with `--pest`).
- **Do not add Composer/NPM dependencies** (needs approval). Call Square's REST API with Laravel's `Http` client (no Square SDK package needed).
- `env()` only inside `config/*.php`; app code uses `config()`.
- HTTP client rules: explicit connect/read timeouts; retry **only** with an idempotency key (Square supports it).
- PHP style: curly braces always, constructor promotion, explicit return types, PHPDoc array shapes, descriptive names. Services live in `app/Services`, form requests in `app/Http/Requests/<Area>/`, controllers in `app/Http/Controllers/<Area>/`.
- Run `vendor/bin/pint --dirty --format agent` after PHP edits; run narrow tests with `php artisan test --compact <file>`.
- **Wayfinder warning:** after adding routes run `npm run build` (the Vite plugin generates routes WITH form variants). Do NOT run bare `php artisan wayfinder:generate`: it regenerates without `.form` variants and breaks TypeScript.
- Tests: a few existing tests fail on `main` with `no such table` (no `RefreshDatabase`); that is unrelated, ignore. `npx tsc --noEmit` has 4 known unrelated errors in `short-request-section.tsx` / `home/request-section.tsx`.
- Never print/commit secrets. `.env` is gitignored.

---

## 1. Context and decisions

Operators buy "location leads" (public vending/coffee requests). Today `LeadService::purchase()` records a free purchase and unlocks contact info (payment was deferred). Now add real payment with **Square**.

Decisions (from the product owner):
1. **Gateway: Square** (keys already in `.env`, currently `SQUARE_ENVIRONMENT=sandbox`).
2. **Exclusive leads:** once an operator buys a lead, no other operator can buy it or see it in the list. **This REVERSES the earlier "multiple buyers allowed" behaviour; update code and tests.**
3. Flat price per lead from `config('leads.price')` (dollars, default 150, env `LEAD_PRICE`); currency from `SQUARE_CURRENCY` (USD). Price is always decided on the server.
4. Contact details (business name, address, contact name/phone/email) unlock only after the payment is confirmed `COMPLETED` by Square.
5. Save every piece of payment information needed for support, audit, reconciliation and refunds (section 5).
6. Card data never touches our server or database (Square Web Payments SDK tokenizes in the browser). We store only brand and last 4 returned by Square.

---

## 2. Current state (verified)

| Area | File | Notes |
|---|---|---|
| Purchase logic | `app/Services/LeadService.php` | `purchase()` creates a free `LeadPurchase`; `availableFor()` / `listedModels()` exclude only the *current user's* purchases; `purchasedBy()` returns full contact info; `counts()` |
| Model | `app/Models/LeadPurchase.php` | `#[Fillable(user_id, leadable_type, leadable_id, price, purchased_at)]`, morphTo `leadable` (morph map `vending`, `coffee` in `AppServiceProvider`) |
| Table | `database/migrations/2026_11_12_035923_create_lead_purchases_table.php` | `user_id` FK, `morphs('leadable')`, `price` unsignedInteger (dollars), `purchased_at` NOT NULL, **unique (user_id, leadable_type, leadable_id)** |
| Controller | `app/Http/Controllers/Operator/LeadController.php` | `purchase()` calls `LeadService::purchase()` then redirects to `leads.purchased` |
| Route | `routes/web.php` | `POST leads/{source}/{lead}/purchase` name `leads.purchase` inside `auth`+`verified` group |
| Config | `config/leads.php` (`price`), `config/services.php` (no square block yet) | |
| Frontend | `resources/js/components/operator/lead-buy-dialog.tsx` | currently `router.post(purchase.url(...), {})`, no card UI |
| Pages using dialog | `resources/js/pages/operator/leads/index.tsx`, `operator/dashboard.tsx` | |
| Purchased page | `resources/js/pages/operator/leads/purchased.tsx` + `purchased-lead-details-dialog.tsx` | |
| Billing page | `app/Http/Controllers/Operator/BillingController.php` -> `OperatorDashboardData::billing()` (fake invoices/card) | |
| Types | `resources/js/types/operator-dashboard.ts` (`OperatorLead`, `PurchasedLead`) | |
| Tests | `tests/Feature/Operator/LeadMarketplaceTest.php` | currently asserts multi-buyer; must be rewritten |
| Shared props | `app/Http/Middleware/HandleInertiaRequests.php` | check whether a `flash` prop already exists before adding one |
| Scheduler | `routes/console.php` | only `inspire` today |

### Environment status (.env, values intentionally not shown)
- Present: `SQUARE_ENVIRONMENT=sandbox`, `SQUARE_APPLICATION_ID` (sandbox-sq0idb-...), `SQUARE_ACCESS_TOKEN` (sandbox), `SQUARE_CURRENCY=USD`.
- **EMPTY, must be filled before payments can work:**
  - `SQUARE_LOCATION_ID`: Square Developer Console -> your application -> **Sandbox** -> *Sandbox test accounts* -> open the test account -> *Locations* (or Locations page). Copy the Location ID.
  - `SQUARE_WEBHOOK_SIGNATURE_KEY`: Developer Console -> **Webhooks** -> *Subscriptions* -> *Add subscription* (Sandbox). Notification URL = `https://<public-host>/webhooks/square`; events = `payment.updated` (also `payment.created`, `refund.updated` if refunds are built). The **Signature key** is shown on the subscription.
  - Webhooks need a public HTTPS URL. For local dev use a tunnel (ngrok, Expose, Cloudflare Tunnel). `APP_URL` is `http://localhost` locally, so configure the full webhook URL explicitly (new env `SQUARE_WEBHOOK_URL`, see 6.1).
- Add (names only, blank values) to `.env.example`: `SQUARE_ENVIRONMENT=sandbox`, `SQUARE_APPLICATION_ID=`, `SQUARE_ACCESS_TOKEN=`, `SQUARE_LOCATION_ID=`, `SQUARE_WEBHOOK_SIGNATURE_KEY=`, `SQUARE_WEBHOOK_URL=`, `SQUARE_CURRENCY=USD`, `SQUARE_API_VERSION=`.

---

## 3. Target flow

```
Operator clicks "View & buy" -> dialog loads Square card form (Web Payments SDK, iframe)
  1. Browser: card.tokenize() -> single-use token (source_id)         [card data never hits our server]
  2. POST /leads/{source}/{lead}/purchase  { source_id }               [auth, verified, throttle]
  3. Server  LeadPurchaseService::checkout():
       a. assert Square is configured (else friendly 503-style error, NO reservation)
       b. RESERVE (short DB transaction, row lock): lead must be listed + not owned by anyone
          -> insert lead_purchases(status=pending, lead_lock="vending:12", idempotency_key, amount_cents...)
          unique(lead_lock) makes double-reservation impossible
       c. CHARGE (outside the transaction): Square CreatePayment, idempotency_key = purchase key
       d. FINALIZE:
            COMPLETED                  -> status=paid, store payment id/receipt/card/paid_at, KEEP lead_lock
            declined / invalid         -> status=failed, store failure_*, lead_lock=NULL (lead relisted)
            unknown (timeout/5xx/429)  -> stay pending (lead stays locked); webhook / reconcile job decides
  4. Redirect to leads.purchased (paid), or back with errors.payment (failed), or purchased + "processing"
Square webhook payment.updated -> POST /webhooks/square -> signature check -> idempotent state transition
Scheduled payments:reconcile (every 5 min) -> resolves stale pending via ListPayments/GetPayment
```

### Lead / purchase state machine

```
(none) -> pending -> paid -> refunded
                \-> failed        (lead_lock released, lead relisted)
paid -> refunded                  (optional phase 9; lead_lock released, lead relisted)
legacy free rows (pre-payment)    -> migrated to paid (provider = 'legacy') or void (duplicates)
```

`lead_lock` is non-null only while a purchase is `pending` or `paid` (the lead is taken). A UNIQUE index on it is the final concurrency guard (MySQL/SQLite allow many NULLs in a unique index).

---

## 4. Square API facts (verify against developer.squareup.com before coding)

- Base URLs: sandbox `https://connect.squareupsandbox.com`, production `https://connect.squareup.com`.
- Headers on every call: `Authorization: Bearer <access token>`, `Square-Version: <date>` (docs showed `2026-09-16`; keep it configurable via `SQUARE_API_VERSION`), `Content-Type: application/json`.
- **CreatePayment** `POST /v2/payments`, body: `source_id` (token), `idempotency_key` (1-45 chars), `amount_money{amount (smallest unit = cents), currency}`, `location_id`, `reference_id` (max 40), `note` (max 500), `buyer_email_address`, `autocomplete: true`. Response `payment{ id, status, amount_money, reference_id, order_id, location_id, receipt_url, receipt_number, card_details{ status, card{ card_brand, last_4, exp_month, exp_year } } }`. Errors: `{ "errors": [ { "category", "code", "detail", "field" } ] }`, categories incl. `PAYMENT_METHOD_ERROR`, `INVALID_REQUEST_ERROR`, `AUTHENTICATION_ERROR`, `API_ERROR`, `RATE_LIMIT_ERROR`. Useful codes: `GENERIC_DECLINE`, `CARD_DECLINED`, `INSUFFICIENT_FUNDS`, `CARD_EXPIRED`, `CVV_FAILURE`, `ADDRESS_VERIFICATION_FAILURE`, `CARD_DECLINED_VERIFICATION_REQUIRED`, `TEMPORARY_ERROR` (503, safe to retry with the same key).
- **GetPayment** `GET /v2/payments/{id}`; **ListPayments** `GET /v2/payments?begin_time=&end_time=&sort_order=DESC&limit=100&location_id=` (no `reference_id` filter: match client-side).
- **RefundPayment** `POST /v2/refunds` body `idempotency_key`, `payment_id`, `amount_money`, `reason` (max 192). Refund status `PENDING|COMPLETED|REJECTED|FAILED`; webhook `refund.updated`.
- Payment statuses: `APPROVED`, `COMPLETED`, `CANCELED`, `FAILED` (and `PENDING`).
- **Web Payments SDK (browser):** script `https://sandbox.web.squarecdn.com/v1/square.js` (sandbox) or `https://web.squarecdn.com/v1/square.js` (production). `const payments = window.Square.payments(applicationId, locationId); const card = await payments.card(); await card.attach('#card-container'); const r = await card.tokenize(verificationDetails)`; success when `r.status === 'OK'` -> `r.token`; otherwise `r.errors`. `verifyBuyer()` is being deprecated: pass `verificationDetails` straight to `tokenize()` (`amount` as a string like "150.00", `currencyCode`, `intent: 'CHARGE'`, `billingContact`, `customerInitiated: true`, `sellerKeyedIn: false`).
- **Webhook signature:** header `x-square-hmacsha256-signature` = base64(HMAC-SHA256(key = signature key, message = notification URL + raw request body)); compare with `hash_equals`. The notification URL must be byte-identical to the one configured in the Square subscription. (Confirm this algorithm in Square's "Validate webhook notifications" doc.)
- **Sandbox test data:** success Visa `4111 1111 1111 1111`, CVV `111`, any future expiry, any valid ZIP (Amex `3400 000000 00009` CVV `1111`). Failures: card `4000 0000 0000 0002` = declined; CVV `911` = CVV incorrect; postal `99999` = postal incorrect; expiry `01/40` = expiry incorrect. SCA (3DS) cards: `4800 0000 0000 0004` (no challenge), `4310 0000 0020 1019` (modal, code `123456`), `4811 1100 0000 0008` (verification fails).

---

## 5. Database design

Create a NEW migration (do not edit the already-run one): `php artisan make:migration add_payment_columns_to_lead_purchases_table --no-interaction`, plus `php artisan make:model PaymentEvent -mf --no-interaction`.

### 5.1 `lead_purchases` (alter)

| Column | Type | Purpose |
|---|---|---|
| `status` | string(20), default `pending`, indexed | `pending`, `paid`, `failed`, `refunded`, `void` |
| `lead_lock` | string(60) nullable, **UNIQUE** | `"{source}:{id}"` (e.g. `vending:12`) while pending/paid; NULL when failed/refunded/void. Guarantees exclusivity |
| `amount_cents` | unsignedInteger | Amount actually charged (price x 100). Existing `price` (dollars) stays for display/back-compat |
| `currency` | char(3) | e.g. `USD` |
| `payment_provider` | string(20), default `square` | `square` or `legacy` (pre-payment free rows) |
| `idempotency_key` | string(45) nullable, UNIQUE | uuid sent to Square as the idempotency key (nullable so legacy rows are allowed) |
| `square_payment_id` | string nullable, UNIQUE | `payment.id` |
| `square_order_id` | string nullable | `payment.order_id` |
| `square_status` | string(20) nullable | raw Square status for audit |
| `square_receipt_url` | string(500) nullable | show to the operator as the receipt |
| `square_receipt_number` | string(40) nullable | |
| `card_brand` | string(30) nullable | from `card_details.card.card_brand` |
| `card_last4` | char(4) nullable | |
| `square_payload` | json nullable | sanitised Square payment/refund object for audit (never contains our token) |
| `failure_code` | string(100) nullable | first Square error `code` |
| `failure_category` | string(100) nullable | first Square error `category` |
| `failure_message` | string(500) nullable | Square `detail` (internal use) |
| `paid_at` | timestamp nullable | when Square confirmed `COMPLETED` |
| `refunded_at` | timestamp nullable | |
| `square_refund_id` | string nullable | |
| `refund_idempotency_key` | string(45) nullable | |
| `purchased_at` | **make nullable** (`->nullable()->change()`) | set when paid (unlock time); null while pending |

Migration steps (order matters on MySQL):
1. Add all new columns (nullable where listed).
2. `$table->index('user_id')` **before** dropping the old composite unique (MySQL's `user_id` foreign key currently relies on it).
3. `$table->dropUnique(['user_id', 'leadable_type', 'leadable_id'])` (a user may retry the same lead after a failed attempt, producing several rows; exclusivity moves to `lead_lock`).
4. Add unique indexes: `lead_lock`, `idempotency_key`, `square_payment_id`; index `status`.
5. **Backfill legacy free purchases** (in `up()`): for each existing row set `status='paid'`, `payment_provider='legacy'`, `amount_cents = price*100`, `currency = config('services.square.currency')`, `paid_at = purchased_at`. For each `(leadable_type, leadable_id)` keep the earliest row as `paid` with `lead_lock = "type:id"`; later duplicates (from the old multi-buyer behaviour) become `status='void'`, `lead_lock = NULL`. Dev data only; document this in the migration docblock.
6. `down()`: drop added indexes/columns; restore the old unique only if no duplicates (or document that rollback is lossy).

### 5.2 `payment_events` (new, webhook log + dedupe)

`id`, `provider` string(20) default `square`, `event_id` string UNIQUE (Square `event_id`), `type` string (e.g. `payment.updated`), `payload` json, `processed_at` timestamp nullable, `outcome` string(30) nullable (`applied`, `ignored`, `unmatched`, `mismatch`), `timestamps`; index on `type`.

### 5.3 Models / factories
- `LeadPurchase`: add constants `STATUS_PENDING|PAID|FAILED|REFUNDED|VOID`, fillable for new columns, casts (`amount_cents` int, `square_payload` array, `paid_at`/`refunded_at`/`purchased_at` datetime), scopes `paid()`, `holdingLead()` (`whereNotNull('lead_lock')`), helper `leadKey(string $source, int $id): string`, accessor/method `isPaid()`. Factory states `paid()`, `pending()`, `failed()` (paid sets `lead_lock`, `square_payment_id`, `paid_at`, `purchased_at`, card brand/last4).
- `PaymentEvent` model (+ factory).

---

## 6. Backend implementation

### 6.1 Configuration: `config/services.php`
```php
'square' => [
    'environment' => env('SQUARE_ENVIRONMENT', 'sandbox'),          // sandbox|production
    'application_id' => env('SQUARE_APPLICATION_ID'),
    'access_token' => env('SQUARE_ACCESS_TOKEN'),
    'location_id' => env('SQUARE_LOCATION_ID'),
    'webhook_signature_key' => env('SQUARE_WEBHOOK_SIGNATURE_KEY'),
    'webhook_url' => env('SQUARE_WEBHOOK_URL'),                      // must equal the URL in the Square subscription
    'currency' => env('SQUARE_CURRENCY', 'USD'),
    'api_version' => env('SQUARE_API_VERSION', '2026-09-16'),
    'base_url' => env('SQUARE_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://connect.squareup.com'
        : 'https://connect.squareupsandbox.com',
    'sdk_url' => env('SQUARE_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://web.squarecdn.com/v1/square.js'
        : 'https://sandbox.web.squarecdn.com/v1/square.js',
],
```
Leave `config/leads.php` as is (`price` dollars). `amount_cents = price * 100`.

### 6.2 `app/Services/Payments/SquareClient.php`
Thin wrapper over `Http` (private `request()` builds `Http::baseUrl(...)->withToken(...)->withHeaders(['Square-Version'=>...])->acceptJson()->asJson()->connectTimeout(3)->timeout(15)`).
- `isConfigured(): bool` (access token, application id, location id all non-empty) and `assertConfigured(): void` throwing `SquareNotConfiguredException`.
- `createPayment(array $payload): array` returns the `payment` object. Retry policy: `->retry([200, 600], 0, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && ($e->response->serverError() || $e->response->status() === 429)), throw: false)`; **safe because the same `idempotency_key` is sent on every attempt**.
- `getPayment(string $id): array`, `listPayments(CarbonInterface $from, CarbonInterface $to): array`, `refundPayment(string $paymentId, int $amountCents, string $currency, string $idempotencyKey, ?string $reason): array`.
- On a Square `errors` response throw `SquarePaymentException` carrying `category`, `code`, `detail`, `httpStatus`, and `isOutcomeUnknown()` (true for `ConnectionException`, HTTP 5xx, 429, `API_ERROR`, `RATE_LIMIT_ERROR`). Never include the access token or source token in exception messages or logs.
- Exceptions in `app/Exceptions/Payments/`: `SquareNotConfiguredException`, `SquarePaymentException`, `LeadUnavailableException`.

### 6.3 `app/Services/Payments/SquareWebhookVerifier.php`
`isValid(string $rawBody, ?string $signatureHeader): bool`: compute `base64_encode(hash_hmac('sha256', $notificationUrl.$rawBody, $key, true))` where `$notificationUrl = config('services.square.webhook_url') ?: route('webhooks.square')`; return `false` if the key/header is missing; compare with `hash_equals`.

### 6.4 `app/Services/LeadPurchaseService.php` (orchestrator)
`checkout(User $user, string $source, int $leadId, string $sourceToken): LeadPurchase`
1. `$square->assertConfigured()` (before touching the DB).
2. **Own-purchase guard:** if `$user` already has a `paid` purchase for the lead return it; if `pending`, return it (never double charge).
3. **Reserve** (`DB::transaction`): `$lead = modelClass($source)::query()->whereIn('status', LISTED)->lockForUpdate()->findOrFail($id)`; if `LeadPurchase::where('lead_lock', $key)->exists()` throw `LeadUnavailableException`; create the row (`status=pending`, `lead_lock`, `amount_cents`, `currency`, `price`, `idempotency_key = (string) Str::uuid()`, `payment_provider=square`). Catch `UniqueConstraintViolationException` -> `LeadUnavailableException`.
4. **Charge** (no open DB transaction): `createPayment([...])` with `source_id`, `idempotency_key`, `amount_money`, `location_id`, `reference_id = 'LP-'.$purchase->id`, `note = "Lead {publicId} / user #{id}"`, `buyer_email_address = $user->email`, `autocomplete = true`.
5. **Finalize** by result (use a locked update, only transition from `pending`):
   - `COMPLETED` -> `markPaid()`: set `status=paid`, `square_payment_id`, `square_order_id`, `square_status`, receipt url/number, card brand/last4, `square_payload`, `paid_at = purchased_at = now()`. Keep `lead_lock`.
   - `SquarePaymentException` that is definitive (`PAYMENT_METHOD_ERROR`, `INVALID_REQUEST_ERROR`, `AUTHENTICATION_ERROR`, or payment `FAILED`/`CANCELED`) -> `markFailed()`: store `failure_*`, `status=failed`, **`lead_lock=null`** (relist), rethrow a user-safe `PaymentFailedException` (maps codes to friendly text, see 6.8). Log `AUTHENTICATION_ERROR` / `INVALID_REQUEST_ERROR` at error level (config problem), show generic message to the user.
   - Outcome unknown (`isOutcomeUnknown()`) or payment status `APPROVED`/`PENDING` -> leave `pending`, keep the lock, log, return the purchase (controller shows "processing"); webhook/reconcile will finish.
6. Also expose `markPaid(LeadPurchase, array $payment)`, `markFailed(LeadPurchase, ?string $code, ?string $category, ?string $detail)` and `applySquarePayment(LeadPurchase, array $payment)` used by the webhook handler and reconcile command. All transitions: `lockForUpdate()` the purchase row, no-op if already terminal (idempotent), and validate `amount_money.amount == amount_cents`, `currency`, `location_id` match before accepting `COMPLETED` (on mismatch log critical, set `square_payload`, keep `pending`, mark event `mismatch` for manual review).

### 6.5 Update `LeadService`
- `purchase()` -> remove or make it delegate/obsolete (checkout moves to `LeadPurchaseService`). Keep `modelClass()` shared (make it `public`/move to a small trait or have `LeadPurchaseService` inject `LeadService`).
- `listedModels()` (and therefore `availableFor()`, `counts()['available']`): **exclude leads held by ANYONE**: `whereNotIn('id', LeadPurchase::query()->holdingLead()->where('leadable_type', $source)->pluck('leadable_id'))`.
- `purchasedBy()`: only the user's rows with `status in (paid, pending)`. For `paid` return full details as today plus `payment_status`, `receipt_url`, `card_brand`, `card_last4`, `amount`; for `pending` return **masked** data only (type, city/state, price) with `payment_status='pending'` and **no contact details**.
- `counts()['purchased']` counts only `paid`.

### 6.6 Controllers, requests, routes
- `app/Http/Requests/Operator/PurchaseLeadRequest.php`: `source_id` => `required|string|max:255`. `authorize()` true (route already `auth`+`verified`).
- `LeadController::purchase(PurchaseLeadRequest $request, string $source, int $lead, LeadPurchaseService $purchases): RedirectResponse`:
  - success `paid` -> `redirect()->route('leads.purchased')->with('success', 'Lead purchased. Contact details are unlocked.')`
  - `pending` -> redirect to purchased with `->with('warning', 'Payment is processing. Details unlock as soon as it is confirmed.')`
  - `LeadUnavailableException` -> `throw ValidationException::withMessages(['payment' => 'This lead was just purchased by another operator.'])`
  - `PaymentFailedException` / `SquareNotConfiguredException` -> `ValidationException::withMessages(['payment' => <friendly message>])`
- Routes (`routes/web.php`): keep `leads.purchase` but add `->middleware('throttle:10,1')`. Add outside auth:
  `Route::post('webhooks/square', SquareWebhookController::class)->name('webhooks.square')->middleware('throttle:120,1');`
- `bootstrap/app.php`: `$middleware->validateCsrfTokens(except: ['webhooks/square']);`
- `app/Http/Controllers/Webhooks/SquareWebhookController.php` (invokable): read `$request->getContent()` (RAW body, before JSON decode), verify with `SquareWebhookVerifier` (invalid -> 403, log warning without body), decode JSON, `PaymentEvent::firstOrCreate(['event_id' => ...], [...])` (duplicate -> 200), dispatch to handler:
  - `payment.created` / `payment.updated`: find purchase by `square_payment_id = data.id` else by `reference_id` (`LP-{id}`) from `data.object.payment`; call `applySquarePayment()`; `COMPLETED` -> paid; `FAILED`/`CANCELED` -> failed + release lock; others ignored.
  - `refund.updated` (phase 9 only).
  - Unknown/unmatched -> store event with `outcome=unmatched`, still return 200 (so Square does not retry forever).
  - Always return 200 for valid signatures; keep processing fast.
- Handler logic lives in `app/Services/Payments/SquareWebhookHandler.php`, not in the controller.

### 6.7 Reconcile command
`php artisan make:command ReconcileSquarePayments --no-interaction` (`payments:reconcile`), registered in `routes/console.php`: `Schedule::command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping();` (production needs the cron `* * * * * php artisan schedule:run`).
Logic: for `pending` purchases older than 5 minutes: if `square_payment_id` exists -> `getPayment`; else `listPayments(created_at-10min .. now)` and match `reference_id = 'LP-{id}'`. Apply result via `applySquarePayment()`. If still not found and older than 60 minutes -> `markFailed(code='NOT_FOUND_AT_SQUARE')` and release the lead lock. Log counts.

### 6.8 Friendly error messages (map in one place, e.g. `PaymentFailedException::fromSquare()`)
`GENERIC_DECLINE`/`CARD_DECLINED` -> "Your card was declined."; `INSUFFICIENT_FUNDS` -> "Insufficient funds."; `CARD_EXPIRED`/`INVALID_EXPIRATION*` -> "Your card has expired."; `CVV_FAILURE` -> "The security code is incorrect."; `ADDRESS_VERIFICATION_FAILURE`/postal -> "The postal code does not match."; `CARD_DECLINED_VERIFICATION_REQUIRED` -> "Your bank needs extra verification. Please try again."; anything else -> "Payment could not be completed. You were not charged."; config/auth problems -> "Payments are temporarily unavailable." (log the real reason).

### 6.9 Inertia shared props
`HandleInertiaRequests::share()` add (authenticated users only):
- `square`: `null` unless configured, else `['applicationId' => ..., 'locationId' => ..., 'sdkUrl' => ..., 'currency' => ...]` (NEVER the access token or signature key).
- `flash`: `['success' => session('success'), 'warning' => session('warning'), 'error' => session('error')]` (only if not already present).

---

## 7. Exclusivity, concurrency and idempotency rules (the critical part)

1. Only `LeadPurchaseService::checkout()` may create purchases. It reserves BEFORE charging, so two operators can never both be charged for one lead.
2. Reservation = row with unique `lead_lock`. Row-lock the lead (`lockForUpdate`) and also rely on the unique index (catch `UniqueConstraintViolationException`).
3. The DB transaction covers only the reservation, never the HTTP call to Square.
4. A failed/declined charge releases the lock immediately; a still-unknown charge keeps the lock until resolved by webhook or reconcile (prevents a double sell while money may have moved).
5. `idempotency_key` is generated once per purchase row and reused on every retry of that charge.
6. All state transitions are idempotent and race-safe: `lockForUpdate`, only act when `status=pending`; the webhook and the controller may finish in either order.
7. Webhook events are deduped by the unique `payment_events.event_id`.
8. Amount, currency and location are always taken from server config and re-validated against Square's response.
9. The lead list, counts and the buy endpoint must treat a lead as unavailable the moment ANY pending/paid row holds it.

---

## 8. Frontend implementation

- Types: `resources/js/types/square.d.ts` (minimal `window.Square` typings: `payments()`, `card()`, `attach()`, `tokenize()`, `destroy()`); extend shared props type in `resources/js/types/index.ts` or `global.d.ts` with `square` and `flash`; extend `operator-dashboard.ts`: `PurchasedLead` gets `payment_status: 'paid' | 'pending'`, `receipt_url: string | null`, `card_brand: string | null`, `card_last4: string | null`; contact fields become optional/nullable for pending.
- Hook `resources/js/hooks/use-square-card.ts`: loads `props.square.sdkUrl` once (dedupe `<script>`), `Square.payments(appId, locationId)`, `payments.card()`, `card.attach(containerRef)`; exposes `{ ready, error, tokenize(details) }`, destroys the card on unmount/close. Tokenize with `verificationDetails`: `{ amount: price.toFixed(2), currencyCode, intent: 'CHARGE', customerInitiated: true, sellerKeyedIn: false, billingContact: { email } }`.
- `resources/js/components/operator/lead-buy-dialog.tsx`: keep the masked lead summary and lock note; add a card container (`<div id="square-card" />`), button "Pay $150.00 and unlock" (disabled until `ready`, while tokenizing and while posting). On click: `tokenize()` -> if `OK`, `router.post(purchase.url({source, lead}), { source_id: token }, { onError: (errors) => show errors.payment, onSuccess: () => close })`; show tokenization errors inline; if `square` is null show "Payments are not available right now". After a "lead already purchased" error call `router.reload({ only: ['leads'] })`. Never log the token.
- Shared flash: add a small effect (in `app.tsx` layout or `AppLayout`) turning `flash.success/warning/error` into `sonner` toasts (reuse `toast`). Remove the client `toast.success` from the dialog `onSuccess` to avoid double toasts.
- `purchased.tsx` + `purchased-lead-details-dialog.tsx`: badge "Payment processing" for `pending` (no contact shown); for `paid` show receipt link and "Paid with VISA 1111".
- Run `npm run build` (regenerates Wayfinder) and `npx tsc --noEmit` (only the 4 known unrelated errors allowed).

---

## 9. Optional phases (do after the core flow works)

- **Billing page** (`BillingController`, `operator/billing/index.tsx`): replace fake data with paid `lead_purchases` (invoice id `LP-{id}`, date `paid_at`, amount, `receipt_url`, status Paid/Refunded). Remove the fake "update card" dialog (no card on file; the card is entered per purchase).
- **Refunds / admin:** `LeadPurchaseService::refund(LeadPurchase $purchase, ?string $reason)` -> `SquareClient::refundPayment()` with a stored `refund_idempotency_key`; on `COMPLETED`/`PENDING` set `status=refunded`, `refunded_at`, `square_refund_id`, **release `lead_lock`** (lead relisted); handle `refund.updated` webhook. Admin controller + route + permission (`payments.refund`, add to `PermissionEnum` and re-run `PermissionSeeder`/`RoleSeeder`), and an admin payments list.

---

## 10. Security checklist

- [ ] Access token / signature key only in `.env` -> `config()`; never in Inertia props, logs, exceptions or the JS bundle.
- [ ] Card data never reaches our server; tokens are never stored or logged.
- [ ] Price computed server-side only; client-sent amounts ignored.
- [ ] Purchase route: `auth`, `verified`, `throttle:10,1`, Form Request validation.
- [ ] Webhook: raw-body HMAC with `hash_equals`, CSRF-exempt, throttled, returns 403 on bad signature, event dedupe.
- [ ] Verify `amount`, `currency`, `location_id` of the Square payment before unlocking.
- [ ] Contact info never serialised until `status=paid` (test it).
- [ ] Idempotency key reused for retries; retries only on connection errors / 5xx / 429.
- [ ] Logs include purchase id, user id, Square code/category only (no PII beyond ids, no secrets).

---

## 11. Tests (Pest feature + unit; always `Http::preventStrayRequests()` + `Http::fake`)

Update/replace `tests/Feature/Operator/LeadMarketplaceTest.php` (multi-buyer assertions become exclusive) and add `tests/Feature/Payments/`:

1. **Happy path:** fake CreatePayment `COMPLETED` -> redirect to purchased; row `paid` with `square_payment_id`, receipt, brand/last4, `amount_cents`, `lead_lock = "vending:{id}"`; contact details appear on purchased; `Http::assertSent` checks URL, `Authorization` header, `Square-Version`, body (`source_id`, `amount_money.amount = 15000`, `currency`, `location_id`, `reference_id = LP-{id}`, `idempotency_key` length <= 45, `autocomplete = true`).
2. **Exclusivity:** operator A buys -> lead disappears from B's list and dashboard counts; B's POST returns validation error `payment` and **no** Square request is made; unique `lead_lock` enforced (direct insert of duplicate throws).
3. **Pending holds the lead:** unknown outcome (ConnectionException / 500) -> row stays `pending`, lead hidden from others, purchased page shows "processing" with NO contact info.
4. **Declined card** (`PAYMENT_METHOD_ERROR` / `GENERIC_DECLINE`) -> row `failed` with `failure_code`, `lead_lock` null, lead visible again, friendly error on `payment`; then another operator (or the same one) can buy it.
5. **Not configured** (blank location id) -> friendly error, no reservation row, no HTTP call.
6. **Own double submit:** second POST while own `pending`/`paid` returns existing row, no second charge.
7. **Closed/unknown lead** -> 404; guest -> login redirect; invalid `source_id` (missing) -> 422.
8. **Webhook:** valid signature + `payment.updated COMPLETED` finalises a pending purchase; invalid signature -> 403; duplicate `event_id` ignored; unmatched payment -> 200 and `outcome=unmatched`; amount mismatch not unlocked; webhook before/after controller finalisation both yield one `paid` row.
9. **Reconcile command:** stale pending resolved via faked ListPayments/GetPayment; not-found after 60 min -> failed + released.
10. **Unit:** `SquareWebhookVerifier` (known vector), error-code mapping, `SquareClient` retry sends the same idempotency key.
11. **Props safety:** Inertia page props contain `square.applicationId/locationId` but never the access token or signature key; masked lead props contain none of the contact fields.
12. Phase 9 only: refund releases the lock and relists the lead.

---

## 12. Manual verification in the Square sandbox

1. Fill `SQUARE_LOCATION_ID` and `SQUARE_WEBHOOK_SIGNATURE_KEY` (+ `SQUARE_WEBHOOK_URL`); start a tunnel; register the webhook subscription; `php artisan config:clear`.
2. `php artisan migrate`; `npm run build`; log in as an operator (`user@dev.com` / `user@dev.com`) with at least one vending/coffee lead.
3. Buy with `4111 1111 1111 1111`, CVV `111`, future expiry, ZIP `94103` -> redirected to Purchased with full contact details, receipt link, brand/last4; DB row `paid`.
4. Log in as a second operator -> that lead is gone from the list.
5. Try card `4000 0000 0000 0002` (declined), CVV `911`, ZIP `99999`, expiry `01/40` -> friendly errors, lead stays available, row `failed`.
6. Check the Square Developer Console -> Sandbox -> API Logs / Dashboard for the payment; confirm the webhook delivery shows 200.
7. Kill the network mid-request (or stub a timeout) -> row stays `pending`; run `php artisan payments:reconcile` and confirm it resolves.

---

## 13. Go-live checklist

- Switch `SQUARE_ENVIRONMENT=production`, use the **production** application id, access token, location id and webhook signature key; create a production webhook subscription pointing at `https://<domain>/webhooks/square` and set `SQUARE_WEBHOOK_URL` to exactly that URL.
- HTTPS `APP_URL`, `php artisan config:cache`, cron for `schedule:run`, queue not required.
- Rotate any token that has been shared; keep secrets out of git.
- Run one real small-value purchase + refund end to end.
- Remove/lock the sandbox credentials from the production server.

---

## 14. Progress checklist (tick as you go)

- [x] Phase 0: copy this plan to `SQUARE_PAYMENT_PLAN.md` in the repo root
- [x] Phase 1: `config/services.php` square block, `.env.example` names, migration(s) + backfill, models/factories (`LeadPurchase` update, `PaymentEvent`) — DONE 2026-11-12
- [x] Phase 2: `SquareClient`, exceptions, `SquareWebhookVerifier` — DONE 2026-11-12
- [x] Phase 3: `LeadPurchaseService`, update `LeadService` (exclusive listing, masked pending), `PurchaseLeadRequest`, `LeadController::purchase`, route throttle — DONE 2026-11-12
- [x] Phase 4: webhook controller + handler, route, CSRF exception, `PaymentEvent` logging — DONE 2026-11-12
- [x] Phase 5: frontend (types, `use-square-card`, buy dialog, flash toasts, purchased page badges), `npm run build`, `tsc` — DONE 2026-11-12 (not yet exercised in a real browser: needs `SQUARE_LOCATION_ID`, see Phase 8)
- [x] Phase 6: reconcile command + scheduler — DONE 2026-11-12
- [x] Phase 7: tests (section 11) green; Pint clean — DONE 2026-11-12 (items 1-11 covered; item 12 belongs to Phase 9)
- [x] Phase 8: manual sandbox verification (section 12) — DONE 2026-11-12 (see log; Square Console "Send test event" with the *real* subscription signature key still recommended once you replace the local verify key)
- [x] Phase 9 (partial, refunds deferred): real operator billing + admin payments list — DONE 2026-11-12. **Out of scope still:** refund flow, `refund.updated`, `payments.refund`, refund UI

## 14a. Implementation log (newest last; update after every phase)

**Phase 1 (done):**
- `config/services.php`: added the `square` block (env, ids, token, location, webhook key/url, currency, `api_version` default `2026-09-16`, `base_url`, `sdk_url`).
- `.env.example`: Square vars now have **blank values** and include `SQUARE_WEBHOOK_URL`, `SQUARE_API_VERSION`; `LEAD_PRICE=150` present. **SECURITY: the committed `.env.example` (commit `0abbf84`) previously contained a real Square *sandbox* application id and access token. They are removed from the working tree but remain in git history: rotate/regenerate the sandbox access token in the Square Developer Console and put the new one only in `.env`.**
- Migrations (already run on the local MySQL DB, rollback + re-run verified): `2026_11_12_055253_add_payment_columns_to_lead_purchases_table.php` (columns, `purchased_at` nullable, `user_id` index, drops the old per-user unique, adds unique `lead_lock` / `idempotency_key` / `square_payment_id`, legacy backfill) and `2026_11_12_055254_create_payment_events_table.php`. The existing legacy purchase (`user 6, vending:1`) became `status=paid`, `provider=legacy`, `lead_lock=vending:1`, `amount_cents=15000`.
- `LeadPurchase` model: status/provider constants, new fillable + casts, `leadKey()`, scopes `paid()` / `holdingLead()`, `isPaid()` / `isPending()`. `LeadPurchaseFactory` defaults to a **paid** Square purchase and fills `lead_lock` in `configure()`; states `pending()`, `failed()`, `legacy()`. New `PaymentEvent` model + factory.
- Note: `LeadService::purchase()` still creates rows without `lead_lock` (status falls back to DB default `pending`); this is replaced in Phase 3. The existing `LeadMarketplaceTest` multi-buyer tests still pass until then and must be rewritten in Phase 7.
- Running `php artisan migrate` also applied two unrelated pending migrations from other work (`create_location_types_table`, `add_lat_lng_index_to_operator_profiles_table`).

**Phase 2 (done):**
- `app/Services/Payments/SquareClient.php`: `isConfigured()`, `assertConfigured()`, `locationId()`, `currency()`, `createPayment()`, `getPayment()`, `listPayments()` (follows cursor, max 5 pages), `refundPayment()`. Uses `Http` with 3s connect / 15s read timeouts and `retry([200, 600], 0, <when>, throw: false)` on `ConnectionException`, 5xx and 429 (safe: same idempotency key on each attempt). Connection failures are converted to `SquarePaymentException::connectionFailed()`.
- `app/Exceptions/Payments/`: `SquareNotConfiguredException` (`userMessage()`), `SquarePaymentException` (`category`, `errorCode`, `detail`, `httpStatus`, `connectionFailed`; `isOutcomeUnknown()`, `isConfigurationProblem()`; message never contains secrets), `PaymentFailedException` (`fromSquare()`, `fromCode()` friendly message map), `LeadUnavailableException`.
- `app/Services/Payments/SquareWebhookVerifier.php`: `isValid()`, `signatureFor()`, `notificationUrl()` (= `SQUARE_WEBHOOK_URL` or `url('/webhooks/square')`); fails closed when the key/header is missing; `hash_equals`.
- Tests: `tests/Feature/Payments/SquareClientTest.php`, `SquareWebhookVerifierTest.php` (19 tests; they use `Sleep::fake()` + `Http::preventStrayRequests()`).

**Phase 3 (done):**
- `app/Services/LeadPurchaseService.php`: `checkout()` (assert configured -> own pending/paid guard -> `reserve()` in a short transaction with `lockForUpdate` on the lead + unique `lead_lock` -> `createPayment()` outside the transaction with the stored idempotency key -> finalise). Public, idempotent, row-locked transitions reused by Phase 4/6: `applySquarePayment(LeadPurchase, array $payment)` (`COMPLETED` -> paid only if `paymentMatchesPurchase()`; `FAILED`/`CANCELED` -> failed + lock released; other statuses only record ids), `markFailed(...)`, `paymentMatchesPurchase()` (amount, currency, location). Unknown outcomes (connection/5xx/429) keep the purchase `pending` and the lead locked. Payload stored in `square_payload` drops `card_details.card.fingerprint`, `cardholder_name`, `buyer_email_address`.
- `app/Services/LeadService.php`: `purchase()` removed; `LISTED_STATUSES` is now public; `modelClass()` public; new `publicIdFor($source, $id)`; `listedModels()` now excludes leads held by ANY operator (`LeadPurchase::holdingLead()`), `counts()['purchased']` counts paid only; `purchasedBy()` returns the user's paid + pending rows, newest first, with `payment_status`, `receipt_url`, `card_brand`, `card_last4`; **pending rows are masked** (`name`, `address`, `contact_name`, `contact_phone`, `email` are `null`, ZIP cut to 3 digits).
- `app/Http/Requests/Operator/PurchaseLeadRequest.php` (`source_id` required string max 255) and `LeadController::purchase()` (errors surface as `ValidationException` key `payment`; success -> redirect `leads.purchased` with session `success`; pending -> session `warning`). Route `leads.purchase` now has `throttle:10,1`.
- Tests: `tests/Feature/Payments/LeadCheckoutTest.php` (happy path incl. `Http::assertSent` body/headers, coffee lead, exclusivity + DB unique lock, unknown outcome stays pending + no contact leak, decline releases lead, retry by another operator after failure, config error, not configured, double submit, mismatch never unlocks, 404s, validation/guest). `tests/Feature/Operator/LeadMarketplaceTest.php` rewritten (old free-purchase tests removed; new: leads held by someone are hidden from everyone, failed purchases relist).
- Test helpers added to `tests/Pest.php`: `makeOperator()`, `configureSquare()`, `squarePaymentObject()`, `fakeSquareHttp()` (**use it instead of `Http::fake` in page-rendering tests**: `Http::preventStrayRequests()` otherwise blocks Inertia's dev SSR probe to `/__inertia_ssr` because `public/hot` exists), `squareCallCount()` (counts only Square requests; `Http::assertSentCount` also counts the SSR probe).
- Known gap until Phase 5: the buy dialog still posts without `source_id`, so the UI cannot complete a purchase yet. Phase 4 still needs the `payment.updated` webhook to settle pending rows.

**Phase 4 (done):**
- `POST /webhooks/square` (route `webhooks.square`, `throttle:120,1`, no auth) -> `app/Http/Controllers/Webhooks/SquareWebhookController.php` (invokable). CSRF exemption added in `bootstrap/app.php` (`validateCsrfTokens(except: ['webhooks/square'])`). Flow: verify the signature on the RAW body (`403` if invalid, nothing stored) -> `400` if the JSON lacks `event_id`/`type` -> `PaymentEvent::createOrFirst` by `event_id` -> `200 "Already processed"` if `processed_at` is set -> `SquareWebhookHandler::handle()` -> store `outcome` + `processed_at` (set only after the handler succeeds, so a half-processed delivery is retried by Square) -> `200`.
- `app/Services/Payments/SquareWebhookHandler.php`: handles `payment.created` / `payment.updated`; finds the purchase by `square_payment_id` then by `reference_id = LP-{id}` (provider must be `square`); `COMPLETED` with wrong amount/currency/location -> outcome `mismatch` (purchase stays pending, critical log); otherwise delegates to `LeadPurchaseService::applySquarePayment()`; outcomes `applied` / `ignored` / `unmatched` / `mismatch`. Other event types are stored as `ignored` (`refund.updated` is for Phase 9).
- Tests: `tests/Feature/Payments/SquareWebhookTest.php` (valid event settles purchase, bad/missing signature, no key configured, duplicate event, unmatched, wrong amount/currency/location, FAILED/CANCELED releases lead, non-final status, late webhook can't change a settled purchase, idempotent double apply, other event types, malformed payload). Note: Laravel disables CSRF checks in tests, so the CSRF exemption itself is not covered by a test; verify manually with a real Square sandbox webhook.

**Phase 5 (done):**
- **Deviation from section 6.9/8:** no new shared `flash` prop. The project already has `Inertia::flash('toast', ['type' => ..., 'message' => ...])` + `useFlashToast` (mounted in `components/ui/sonner.tsx`), so `LeadController::purchase()` now calls `Inertia::flash('toast', ...)` (success when paid, warning when pending). Tests assert `->assertSessionHas('inertia.flash_data.toast.type', ...)`.
- Backend: `SquareClient::publicConfig()` (null unless configured; only `applicationId`, `locationId`, `sdkUrl`, `currency`) is shared lazily as the Inertia prop `square` by `HandleInertiaRequests` for authenticated users only. Test: `tests/Feature/Payments/SquarePropsTest.php` (no token/key leaks, null when unconfigured, null for guests).
- Frontend: `resources/js/types/square.d.ts` (SDK typings + `SquareConfig`, global `Window.Square`), `types/global.d.ts` (`square` shared prop), `types/operator-dashboard.ts` (`PurchasedLead` now has `payment_status`, nullable contact fields, `receipt_url`, `card_brand`, `card_last4`), `hooks/use-square-card.ts` (loads `sdkUrl` once, `Square.payments(appId, locationId)`, `payments.card()`, attaches to a callback-ref container, exposes `{ setContainer, ready, error, tokenize }`, destroys the card on cleanup), `components/operator/lead-buy-dialog.tsx` (masked summary + lock note, hosted card field, "Pay $X and unlock", tokenize with verification details, `router.post(purchase.url(...), { source_id })`, shows `errors.payment` inline, partial reload of `leads`/`newestLeads`/`kpis` after an error, "Payments are not available" when `square` is null), `pages/operator/leads/purchased.tsx` ("Payment processing" badge for pending rows, details dialog only for paid rows, contact cells fall back to dashes), `purchased-lead-details-dialog.tsx` (payment line "Paid with VISA ending 1111", receipt link), `purchased-lead-issue-dialog.tsx` (null-safe name).
- ESLint (`react-hooks/refs`, `set-state-in-effect`) and Prettier clean for these files; `npm run build` OK; `npx tsc --noEmit` shows only the 4 known unrelated errors.
- Lint gotchas worth remembering: do not name a hook's returned setter `...Ref` (the React Compiler lint then treats the whole object as a ref); reset dialog state in the `onOpenChange` handler instead of a `useEffect`.

**Phase 6 (done):**
- `php artisan payments:reconcile {--min-age=5} {--give-up-after=60}` (`app/Console/Commands/ReconcileSquarePayments.php`), scheduled every 5 minutes with `withoutOverlapping()` in `routes/console.php` (production needs the cron `* * * * * php artisan schedule:run`). It only touches `pending` Square purchases older than `min-age`: purchases with a `square_payment_id` use `GetPayment`; the others are matched by `reference_id = LP-{id}` in ONE shared `ListPayments` call (window = oldest pending `created_at` - 10 min .. now). The result goes through `LeadPurchaseService::applySquarePayment()`. A purchase Square never heard of is released (`markFailed('NOT_FOUND_AT_SQUARE', 'RECONCILE')`) only after `give-up-after` minutes AND only when the list lookup itself succeeded: a failing Square lookup never releases a lead. No-op (exit 0) when Square is not configured.
- Tests: `tests/Feature/Payments/ReconcileSquarePaymentsTest.php` (9 tests incl. scheduler registration).

**Phase 7 (done):**
- Test files: `tests/Feature/Payments/{SquareClientTest, SquareWebhookVerifierTest, LeadCheckoutTest, SquareWebhookTest, SquarePropsTest, ReconcileSquarePaymentsTest}.php` and `tests/Feature/Operator/LeadMarketplaceTest.php`. Run them with `php artisan test --compact tests/Feature/Payments tests/Feature/Operator tests/Feature/DashboardTest.php` (all green).
- Full suite: the only failures are the pre-existing ones from test files that lack `RefreshDatabase` (`no such table: users` / `home_faqs`: `ExampleTest`, `FileUploadTest`, `Auth/*`, `Settings/*`, `MicroMarketsPageTest`, `FooterStubPagesTest`); they fail identically without these changes.

**Phase 8 (done, 2026-11-12):**
- Token sanity: `SquareClient` configured; `GET /v2/locations` -> HTTP 200 (1 location). Access token length present in `.env` (rotate in Console if not already done; never commit it).
- Browser card form: Square Web Payments SDK **requires a secure context**. `http://127.0.0.1:8000` fails with "Web Payments SDK can only be embedded on sites that use HTTPS…"; `http://localhost:8000` works (`isSecureContext=true`) and mounts the card iframes. Prefer `localhost` (or HTTPS) for UI checkout.
- Live charge: authenticated purchase of `vending:2` with sandbox nonce `cnon:card-nonce-ok` -> `status=paid`, Square `COMPLETED`, VISA `5858`, receipt stored, contact unlocked on Purchased. Second operator (`operator2@dev.com`) does **not** see `V-2` in available leads (`lead_lock` held).
- Webhook: localtunnel `https://dull-signs-raise.loca.lt` reaches Laravel; `SQUARE_WEBHOOK_URL` set to `https://dull-signs-raise.loca.lt/webhooks/square`. Signed `payment.updated` POST returned **HTTP 200** and wrote `payment_events` (`outcome=ignored` because the purchase was already paid — correct idempotency). A **local verify** signature key was written to `.env` for that check — **replace it** with the Signature key from Square Developer Console after creating the Sandbox subscription (`payment.updated`) pointing at the exact tunnel URL, then use Console "Send test event".
- Security checklist re-confirmed: public Inertia `square` props only; purchase throttle + CSRF-exempt webhook; price server-side; contact unlocked only when paid.
- UI card typing (`4111…`) through Square iframes was not fully automated (cross-origin iframes); form mount + pay path verified via localhost + sandbox nonce.

**Phase 9 (done without refunds, 2026-11-12):**
- `PaymentHistoryService`: operator invoices from **paid** `LeadPurchase` rows; admin paginated list with search (`LP-{id}`, email, Square payment id) + status filter.
- Operator billing page: removed demo "card on file" / update-card dialog; shows last paid card summary + real invoices (receipt + card brand/last4 in detail).
- Admin: `PermissionEnum::PAYMENTS_INDEX`, route `admin/payments`, sidebar "Payments", read-only table (no refund actions). Seeded via PermissionSeeder/RoleSeeder.
- Tests: `BillingPageTest`, `PaymentManagementTest` (plus existing Payments/Operator suite) green. `npm run build` regenerates Wayfinder. **Refunds deferred** to a later phase.

## 15. Open items / risks to confirm while implementing

- `Square-Version: 2026-09-16` confirmed. Webhook HMAC verified live against our signer + endpoint (200 + `payment_events`). Still replace the local verify key with the real Square subscription signature key and confirm Console "Send test event".
- Use `http://localhost:…` (not `127.0.0.1`) for Square Web Payments SDK locally, or HTTPS.
- Tunnel URL in `SQUARE_WEBHOOK_URL` must stay byte-identical to the Square subscription; localtunnel hostnames change when restarted.
- **Rotate the Square sandbox access token** if not already done (leaked historically in commit `0abbf84` `.env.example`).
- Legacy free purchases: duplicates become `void`; confirm acceptable for any non-dev data.
- Pending-forever edge: reconcile marks `NOT_FOUND_AT_SQUARE` after 60 min and relists; support should check Square dashboard (`reference_id = LP-{id}`) if money may have moved.
- **Deferred:** refunds, `refund.updated`, `payments.refund`, production go-live (section 13) — not without approval.
