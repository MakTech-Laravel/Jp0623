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
        Schema::create('coffee_service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('zip_code');
            $table->string('contact_name');
            $table->string('phone');
            $table->string('email');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('coffee_service_requests');
    }
};
