<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reverse the parallel operator_machine_types stack; categories live on machine_categories.
     */
    public function up(): void
    {
        Schema::dropIfExists('operator_machine_types');
    }

    /**
     * Recreate the table if this migration is rolled back.
     */
    public function down(): void
    {
        Schema::create('operator_machine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
