<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultPrice = (int) config('leads.price', 150);

        Schema::table('vending_requests', function (Blueprint $table) use ($defaultPrice): void {
            $table->unsignedInteger('price')->default($defaultPrice)->after('terms_accepted');
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) use ($defaultPrice): void {
            $table->unsignedInteger('price')->default($defaultPrice)->after('terms_accepted');
        });

        DB::table('vending_requests')->whereNull('price')->update(['price' => $defaultPrice]);
        DB::table('coffee_service_requests')->whereNull('price')->update(['price' => $defaultPrice]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vending_requests', function (Blueprint $table): void {
            $table->dropColumn('price');
        });

        Schema::table('coffee_service_requests', function (Blueprint $table): void {
            $table->dropColumn('price');
        });
    }
};
