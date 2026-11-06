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
        // Normalize non-numeric / empty values before changing column types.
        DB::table('vending_requests')->get(['id', 'employees_on_site', 'daily_visitors'])->each(function ($row): void {
            $employees = is_numeric($row->employees_on_site) ? (int) $row->employees_on_site : null;
            $visitors = is_numeric($row->daily_visitors) ? (int) $row->daily_visitors : null;

            DB::table('vending_requests')->where('id', $row->id)->update([
                'employees_on_site' => $employees,
                'daily_visitors' => $visitors,
            ]);
        });

        Schema::table('vending_requests', function (Blueprint $table) {
            $table->unsignedInteger('employees_on_site')->nullable()->change();
            $table->unsignedInteger('daily_visitors')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vending_requests', function (Blueprint $table) {
            $table->string('employees_on_site')->nullable()->change();
            $table->string('daily_visitors')->nullable()->change();
        });
    }
};
