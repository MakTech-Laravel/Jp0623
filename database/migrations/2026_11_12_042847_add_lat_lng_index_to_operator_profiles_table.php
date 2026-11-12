<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            $table->index(['latitude', 'longitude'], 'operator_profiles_lat_lng_index');
        });
    }

    public function down(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            $table->dropIndex('operator_profiles_lat_lng_index');
        });
    }
};
