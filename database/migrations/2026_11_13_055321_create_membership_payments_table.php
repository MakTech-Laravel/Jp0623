<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_membership_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('USD');
            $table->string('status', 32)->default('pending');
            $table->string('payment_provider', 32)->default('square');
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('square_payment_id')->nullable()->index();
            $table->string('square_order_id')->nullable();
            $table->string('square_status')->nullable();
            $table->string('square_receipt_url')->nullable();
            $table->string('square_receipt_number')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->json('square_payload')->nullable();
            $table->string('failure_code')->nullable();
            $table->string('failure_category')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};
