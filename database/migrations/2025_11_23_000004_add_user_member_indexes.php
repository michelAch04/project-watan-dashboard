<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Phase 4: User and Member Table Indexes
     * - users: Manager hierarchy and authentication
     * - pw_members: Member search and filtering
     */
    public function up(): void
    {
        // users - MEDIUM: Manager hierarchy and active user queries
        Schema::table('users', function (Blueprint $table) {
            $table->index('manager_id', 'idx_users_manager');
            $table->index('cancelled', 'idx_users_cancelled');
            $table->index(['username', 'cancelled'], 'idx_users_username_cancelled');
        });

        // pw_members - HIGH: Member search and status filtering
        Schema::table('pw_members', function (Blueprint $table) {
            $table->index('is_active', 'idx_pw_members_active');
            $table->index('cancelled', 'idx_pw_members_cancelled');
            $table->index('office_status', 'idx_pw_members_office_status');
            $table->index('phone', 'idx_pw_members_phone');
            $table->index('email', 'idx_pw_members_email');
            $table->index(['first_name', 'last_name'], 'idx_pw_members_name');
            $table->index(['is_active', 'cancelled'], 'idx_pw_members_active_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_manager');
            $table->dropIndex('idx_users_cancelled');
            $table->dropIndex('idx_users_username_cancelled');
        });

        // Drop pw_members indexes
        Schema::table('pw_members', function (Blueprint $table) {
            $table->dropIndex('idx_pw_members_active');
            $table->dropIndex('idx_pw_members_cancelled');
            $table->dropIndex('idx_pw_members_office_status');
            $table->dropIndex('idx_pw_members_phone');
            $table->dropIndex('idx_pw_members_email');
            $table->dropIndex('idx_pw_members_name');
            $table->dropIndex('idx_pw_members_active_cancelled');
        });
    }
};
