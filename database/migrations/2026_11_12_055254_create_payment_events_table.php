<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 20)->default('square');
            $table->string('event_id')->unique();
            $table->string('type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->string('outcome', 30)->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
