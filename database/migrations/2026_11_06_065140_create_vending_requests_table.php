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
        Schema::create('vending_requests', function (Blueprint $table) {
            $table->id();
            $table->string('location_type');
            $table->string('business_name');
            $table->string('address_or_zip');
            $table->string('employees_on_site')->nullable();
            $table->string('daily_visitors')->nullable();
            $table->string('placement');
            $table->text('space_notes')->nullable();
            $table->string('food_on_site');
            $table->string('comparing_quotes');
            $table->json('machine_interests');
            $table->string('machine_count');
            $table->string('existing_vending');
            $table->text('switch_reason')->nullable();
            $table->string('contact_name');
            $table->string('job_title')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->boolean('terms_accepted')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vending_requests');
    }
};
