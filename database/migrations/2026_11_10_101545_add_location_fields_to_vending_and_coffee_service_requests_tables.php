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
        Schema::table('vending_requests', function (Blueprint $table) {
            $table->string('address')->nullable()->after('address_or_zip');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->decimal('latitude', 10, 7)->nullable()->after('postal_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) {
            $table->string('address')->nullable()->after('zip_code');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->decimal('latitude', 10, 7)->nullable()->after('postal_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vending_requests', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'city',
                'state',
                'postal_code',
                'latitude',
                'longitude',
            ]);
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'city',
                'state',
                'postal_code',
                'latitude',
                'longitude',
            ]);
        });
    }
};
