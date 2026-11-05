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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_category_id')
                ->constrained('machine_categories')
                ->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kind');
            $table->json('tags')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('capacity')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('best_for')->nullable();
            $table->string('status')->default('Available nationwide');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['machine_category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
