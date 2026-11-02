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
        Schema::create('operator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name');
            $table->string('phone')->nullable();
            $table->boolean('owns_machines')->default(false);
            $table->json('machine_types');
            $table->string('machines_needed');
            $table->string('primary_city');
            $table->timestamp('terms_accepted_at');
            $table->timestamp('email_code_confirmed_at')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_profiles');
    }
};
