<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Phase 1: Critical Performance Indexes
     * - voters_list: Large dataset with millions of records
     * - request_headers: High transaction volume
     * - inbox_notifications: Real-time notifications
     */
    public function up(): void
    {
        // voters_list - CRITICAL: Huge dataset optimization
        Schema::table('voters_list', function (Blueprint $table) {
            $table->index('cancelled', 'idx_voters_list_cancelled');
            $table->index('phone', 'idx_voters_list_phone');
            $table->index('mother_full_name', 'idx_voters_list_mother_name');
            $table->index('father_name', 'idx_voters_list_father_name');
            $table->index(['city_id', 'cancelled'], 'idx_voters_list_city_cancelled');
        });

        // request_headers - CRITICAL: High transaction volume
        Schema::table('request_headers', function (Blueprint $table) {
            $table->index('cancelled', 'idx_request_headers_cancelled');
            $table->index('request_status_id', 'idx_request_headers_status');
            $table->index('reference_member_id', 'idx_request_headers_ref_member');
            $table->index('ready_date', 'idx_request_headers_ready_date');
            $table->index(['request_status_id', 'cancelled'], 'idx_request_headers_status_cancelled');
            $table->index(['sender_id', 'cancelled'], 'idx_request_headers_sender_cancelled');
            $table->index(['request_date', 'cancelled'], 'idx_request_headers_date_cancelled');
        });

        // inbox_notifications - CRITICAL: Real-time notification queries
        Schema::table('inbox_notifications', function (Blueprint $table) {
            $table->index('type', 'idx_inbox_notifications_type');
            $table->index('request_id', 'idx_inbox_notifications_request');
            $table->index(['user_id', 'type'], 'idx_inbox_notifications_user_type');
            $table->index(['user_id', 'created_at'], 'idx_inbox_notifications_user_created');
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_inbox_notifications_user_read_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop voters_list indexes
        Schema::table('voters_list', function (Blueprint $table) {
            $table->dropIndex('idx_voters_list_cancelled');
            $table->dropIndex('idx_voters_list_phone');
            $table->dropIndex('idx_voters_list_mother_name');
            $table->dropIndex('idx_voters_list_father_name');
            $table->dropIndex('idx_voters_list_city_cancelled');
        });

        // Drop request_headers indexes
        Schema::table('request_headers', function (Blueprint $table) {
            $table->dropIndex('idx_request_headers_cancelled');
            $table->dropIndex('idx_request_headers_status');
            $table->dropIndex('idx_request_headers_ref_member');
            $table->dropIndex('idx_request_headers_ready_date');
            $table->dropIndex('idx_request_headers_status_cancelled');
            $table->dropIndex('idx_request_headers_sender_cancelled');
            $table->dropIndex('idx_request_headers_date_cancelled');
        });

        // Drop inbox_notifications indexes
        Schema::table('inbox_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_inbox_notifications_type');
            $table->dropIndex('idx_inbox_notifications_request');
            $table->dropIndex('idx_inbox_notifications_user_type');
            $table->dropIndex('idx_inbox_notifications_user_created');
            $table->dropIndex('idx_inbox_notifications_user_read_created');
        });
    }
};
