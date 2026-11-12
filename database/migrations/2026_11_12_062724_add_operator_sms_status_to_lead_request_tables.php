<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vending_requests', function (Blueprint $table) {
            $table->string('operator_sms_status')->default('pending')->after('status');
            $table->unsignedInteger('operator_sms_sent_count')->default(0)->after('operator_sms_status');
            $table->unsignedInteger('operator_sms_failed_count')->default(0)->after('operator_sms_sent_count');
            $table->string('operator_sms_provider_status')->nullable()->after('operator_sms_failed_count');
            $table->string('operator_sms_note')->nullable()->after('operator_sms_provider_status');
            $table->timestamp('operator_sms_notified_at')->nullable()->after('operator_sms_note');
            $table->index('operator_sms_status');
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) {
            $table->string('operator_sms_status')->default('pending')->after('status');
            $table->unsignedInteger('operator_sms_sent_count')->default(0)->after('operator_sms_status');
            $table->unsignedInteger('operator_sms_failed_count')->default(0)->after('operator_sms_sent_count');
            $table->string('operator_sms_provider_status')->nullable()->after('operator_sms_failed_count');
            $table->string('operator_sms_note')->nullable()->after('operator_sms_provider_status');
            $table->timestamp('operator_sms_notified_at')->nullable()->after('operator_sms_note');
            $table->index('operator_sms_status');
        });
    }

    public function down(): void
    {
        Schema::table('vending_requests', function (Blueprint $table) {
            $table->dropIndex(['operator_sms_status']);
            $table->dropColumn([
                'operator_sms_status',
                'operator_sms_sent_count',
                'operator_sms_failed_count',
                'operator_sms_provider_status',
                'operator_sms_note',
                'operator_sms_notified_at',
            ]);
        });

        Schema::table('coffee_service_requests', function (Blueprint $table) {
            $table->dropIndex(['operator_sms_status']);
            $table->dropColumn([
                'operator_sms_status',
                'operator_sms_sent_count',
                'operator_sms_failed_count',
                'operator_sms_provider_status',
                'operator_sms_note',
                'operator_sms_notified_at',
            ]);
        });
    }
};
