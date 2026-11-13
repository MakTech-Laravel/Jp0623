<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 16)->default('free');
            $table->unsignedInteger('monthly_price')->default(29);
            $table->unsignedSmallInteger('grace_days')->default(0);
            $table->timestamps();
        });

        DB::table('membership_settings')->insert([
            'mode' => 'free',
            'monthly_price' => (int) config('membership.default_monthly_price', 29),
            'grace_days' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_settings');
    }
};
