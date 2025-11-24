<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            // Optimization: Removed single 'cancelled' index (Low cardinality)
        });

        // Username composite index with prefix length to avoid key length issues
        DB::statement('ALTER TABLE users ADD INDEX idx_users_username_cancelled (username(100), cancelled)');

        // pw_members - HIGH: Member search and status filtering
        Schema::table('pw_members', function (Blueprint $table) {
            // 1. Join Performance (Critical for PwMember::with('voter'))
            $table->index('voter_id', 'idx_pw_members_voter_id');

            // 2. Status Filters (Composite is faster than single indexes)
            // Covers: WHERE is_active = 1 AND cancelled = 0
            $table->index(['is_active', 'cancelled'], 'idx_pw_members_status');

            // Covers: WHERE office_status = 'manager' AND cancelled = 0
            $table->index(['office_status', 'cancelled'], 'idx_pw_members_office_cancelled');
        });

        // String indexes with prefix lengths to avoid key length issues
        DB::statement('ALTER TABLE pw_members ADD INDEX idx_pw_members_phone (phone(50))');
        DB::statement('ALTER TABLE pw_members ADD INDEX idx_pw_members_email (email(100))');

        // Composite name index with prefix lengths: (50+50+50)*4 = 600 bytes
        DB::statement('ALTER TABLE pw_members ADD INDEX idx_pw_members_name_sort (first_name(50), father_name(50), last_name(50))');

        // FULL TEXT SEARCH (The "Google-like" search optimization)
        DB::statement('ALTER TABLE pw_members ADD FULLTEXT idx_pw_members_search (first_name, father_name, last_name)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_manager');
            $table->dropIndex('idx_users_username_cancelled');
        });

        // Drop pw_members indexes
        Schema::table('pw_members', function (Blueprint $table) {
            $table->dropIndex('idx_pw_members_phone');
            $table->dropIndex('idx_pw_members_email');
            $table->dropIndex('idx_pw_members_status');
            $table->dropIndex('idx_pw_members_office_cancelled');
            $table->dropIndex('idx_pw_members_name_sort');
            $table->dropIndex('idx_pw_members_voter_id');
            
            // Drop Full Text Index
            $table->dropIndex('idx_pw_members_search');
        });
    }
};