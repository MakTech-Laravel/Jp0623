<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Square payment tracking to lead purchases and makes leads exclusive.
 *
 * `lead_lock` ("vending:12") is non-null only while a purchase is pending or
 * paid and is UNIQUE, so a lead can be held by exactly one operator.
 *
 * Legacy free purchases (created before payments existed) are backfilled as
 * `paid` with provider `legacy`. If several operators "bought" the same lead
 * under the old multi-buyer behaviour, only the earliest keeps the lead; the
 * rest become `void` and lose access (dev data only).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_purchases', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('price');
            $table->string('lead_lock', 60)->nullable()->after('status');
            $table->unsignedInteger('amount_cents')->default(0)->after('lead_lock');
            $table->char('currency', 3)->default('USD')->after('amount_cents');
            $table->string('payment_provider', 20)->default('square')->after('currency');
            $table->string('idempotency_key', 45)->nullable()->after('payment_provider');
            $table->string('square_payment_id')->nullable()->after('idempotency_key');
            $table->string('square_order_id')->nullable()->after('square_payment_id');
            $table->string('square_status', 20)->nullable()->after('square_order_id');
            $table->string('square_receipt_url', 500)->nullable()->after('square_status');
            $table->string('square_receipt_number', 40)->nullable()->after('square_receipt_url');
            $table->string('card_brand', 30)->nullable()->after('square_receipt_number');
            $table->char('card_last4', 4)->nullable()->after('card_brand');
            $table->json('square_payload')->nullable()->after('card_last4');
            $table->string('failure_code', 100)->nullable()->after('square_payload');
            $table->string('failure_category', 100)->nullable()->after('failure_code');
            $table->string('failure_message', 500)->nullable()->after('failure_category');
            $table->timestamp('paid_at')->nullable()->after('failure_message');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->string('square_refund_id')->nullable()->after('refunded_at');
            $table->string('refund_idempotency_key', 45)->nullable()->after('square_refund_id');
            $table->timestamp('purchased_at')->nullable()->change();

            // MySQL's user_id foreign key relies on the old composite unique;
            // give it its own index before that unique is dropped below.
            $table->index('user_id');
        });

        $this->backfillLegacyPurchases();

        Schema::table('lead_purchases', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'leadable_type', 'leadable_id']);

            $table->unique('lead_lock');
            $table->unique('idempotency_key');
            $table->unique('square_payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Lossy: only paid purchases can be restored under the old per-user unique.
     */
    public function down(): void
    {
        DB::table('lead_purchases')
            ->where('status', '!=', 'paid')
            ->delete();

        DB::table('lead_purchases')
            ->whereNull('purchased_at')
            ->update(['purchased_at' => DB::raw('created_at')]);

        Schema::table('lead_purchases', function (Blueprint $table) {
            $table->dropUnique(['lead_lock']);
            $table->dropUnique(['idempotency_key']);
            $table->dropUnique(['square_payment_id']);
            $table->dropIndex(['status']);

            $table->unique(['user_id', 'leadable_type', 'leadable_id']);
        });

        Schema::table('lead_purchases', function (Blueprint $table) {
            $table->dropIndex(['user_id']);

            $table->dropColumn([
                'status',
                'lead_lock',
                'amount_cents',
                'currency',
                'payment_provider',
                'idempotency_key',
                'square_payment_id',
                'square_order_id',
                'square_status',
                'square_receipt_url',
                'square_receipt_number',
                'card_brand',
                'card_last4',
                'square_payload',
                'failure_code',
                'failure_category',
                'failure_message',
                'paid_at',
                'refunded_at',
                'square_refund_id',
                'refund_idempotency_key',
            ]);

            $table->timestamp('purchased_at')->nullable(false)->change();
        });
    }

    private function backfillLegacyPurchases(): void
    {
        $currency = (string) config('services.square.currency', 'USD');
        $held = [];

        DB::table('lead_purchases')
            ->orderBy('purchased_at')
            ->orderBy('id')
            ->get()
            ->each(function (object $purchase) use (&$held, $currency): void {
                $leadKey = $purchase->leadable_type.':'.$purchase->leadable_id;
                $keepsLead = ! isset($held[$leadKey]);
                $held[$leadKey] = true;

                DB::table('lead_purchases')
                    ->where('id', $purchase->id)
                    ->update([
                        'status' => $keepsLead ? 'paid' : 'void',
                        'lead_lock' => $keepsLead ? $leadKey : null,
                        'payment_provider' => 'legacy',
                        'amount_cents' => (int) $purchase->price * 100,
                        'currency' => $currency,
                        'paid_at' => $keepsLead ? $purchase->purchased_at : null,
                    ]);
            });
    }
};
