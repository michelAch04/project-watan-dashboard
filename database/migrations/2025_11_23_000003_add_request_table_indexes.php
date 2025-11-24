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
     * Phase 3: Request Table Indexes
     * - humanitarian_requests: Request workflow optimization
     * - public_requests: Search and filtering
     * - diapers_requests: Voter-based queries
     * - diapers_request_items: Size-based filtering
     */
    public function up(): void
    {
        // humanitarian_requests - HIGH: Request type filtering and duplicate checks
        Schema::table('humanitarian_requests', function (Blueprint $table) {
            $table->index('subtype', 'idx_humanitarian_subtype');
            $table->index(['voter_id', 'request_header_id'], 'idx_humanitarian_voter_request');
            $table->index(['budget_id', 'amount'], 'idx_humanitarian_budget_amount');
        });

        // public_requests - HIGH: Search and budget allocation
        Schema::table('public_requests', function (Blueprint $table) {
            $table->index(['city_id', 'budget_id'], 'idx_public_city_budget');
        });

        // String indexes with prefix lengths to avoid key length issues
        DB::statement('ALTER TABLE public_requests ADD INDEX idx_public_requester_name (requester_full_name(100))');
        DB::statement('ALTER TABLE public_requests ADD INDEX idx_public_requester_phone (requester_phone(50))');

        // diapers_requests - HIGH: Voter and budget queries
        Schema::table('diapers_requests', function (Blueprint $table) {
            $table->index(['voter_id', 'diaper_budget_id'], 'idx_diapers_voter_budget');
        });

        // diapers_request_items - MEDIUM: Size filtering and grouping
        Schema::table('diapers_request_items', function (Blueprint $table) {
            $table->index('size', 'idx_diapers_items_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop humanitarian_requests indexes
        Schema::table('humanitarian_requests', function (Blueprint $table) {
            $table->dropIndex('idx_humanitarian_subtype');
            $table->dropIndex('idx_humanitarian_voter_request');
            $table->dropIndex('idx_humanitarian_budget_amount');
        });

        // Drop public_requests indexes
        Schema::table('public_requests', function (Blueprint $table) {
            $table->dropIndex('idx_public_requester_name');
            $table->dropIndex('idx_public_requester_phone');
            $table->dropIndex('idx_public_city_budget');
        });

        // Drop diapers_requests indexes
        Schema::table('diapers_requests', function (Blueprint $table) {
            $table->dropIndex('idx_diapers_voter_budget');
        });

        // Drop diapers_request_items indexes
        Schema::table('diapers_request_items', function (Blueprint $table) {
            $table->dropIndex('idx_diapers_items_size');
        });
    }
};
