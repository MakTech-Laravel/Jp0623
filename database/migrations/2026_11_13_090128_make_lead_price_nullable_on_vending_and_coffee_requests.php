<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vending_requests', function (Blueprint $table): void {
            $table->unsignedInteger('price')->nullable()->default(null)->change();
        });

        Schema::table('coffee_service_requests', function (Blueprint $table): void {
            $table->unsignedInteger('price')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        $defaultPrice = (int) config('leads.price', 150);

        Schema::table('vending_requests', function (Blueprint $table) use ($defaultPrice): void {
            $table->unsignedInteger('price')->nullable(false)->default($defaultPrice)->change();
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) use ($defaultPrice): void {
            $table->unsignedInteger('price')->nullable(false)->default($defaultPrice)->change();
        });
    }
};
