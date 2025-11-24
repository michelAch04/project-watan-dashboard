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
     * Phase 5: Location and Budget Table Indexes
     * - governorates, districts, zones, cities: Location hierarchy
     * - budgets, diaper_budgets: Budget management and refill tracking
     * - public_institutions: Institution lookups
     * - request_statuses: Status ordering
     */
    public function up(): void
    {
        // governorates - MEDIUM: Location filtering
        Schema::table('governorates', function (Blueprint $table) {
            $table->index('cancelled', 'idx_governorates_cancelled');
        });

        // districts - MEDIUM: Location hierarchy
        Schema::table('districts', function (Blueprint $table) {
            $table->index('cancelled', 'idx_districts_cancelled');
            $table->index(['governorate_id', 'cancelled'], 'idx_districts_gov_cancelled');
        });

        // zones - MEDIUM: Zone-based queries
        Schema::table('zones', function (Blueprint $table) {
            $table->index('cancelled', 'idx_zones_cancelled');
            $table->index(['district_id', 'cancelled'], 'idx_zones_district_cancelled');
        });

        // cities - MEDIUM: City filtering
        Schema::table('cities', function (Blueprint $table) {
            $table->index('cancelled', 'idx_cities_cancelled');
            $table->index(['zone_id', 'cancelled'], 'idx_cities_zone_cancelled');
        });

        // budgets - MEDIUM: Budget allocation and refill tracking
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('cancelled', 'idx_budgets_cancelled');
            $table->index('last_refill_date', 'idx_budgets_refill_date');
            $table->index(['zone_id', 'cancelled'], 'idx_budgets_zone_cancelled');
        });

        // diaper_budgets - MEDIUM: Diaper budget management
        Schema::table('diaper_budgets', function (Blueprint $table) {
            $table->index('cancelled', 'idx_diaper_budgets_cancelled');
            $table->index('last_refill_date', 'idx_diaper_budgets_refill_date');
            $table->index(['zone_id', 'cancelled'], 'idx_diaper_budgets_zone_cancelled');
        });

        // public_institutions - MEDIUM: Institution search
        Schema::table('public_institutions', function (Blueprint $table) {
            $table->index('cancelled', 'idx_public_inst_cancelled');
            $table->index(['city_id', 'cancelled'], 'idx_public_inst_city_cancelled');
        });

        // String index with prefix length to avoid key length issues
        DB::statement('ALTER TABLE public_institutions ADD INDEX idx_public_inst_name (name(100))');

        // request_statuses - LOW: Status ordering
        Schema::table('request_statuses', function (Blueprint $table) {
            $table->index('order', 'idx_request_statuses_order');
            $table->index('cancelled', 'idx_request_statuses_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop governorates indexes
        Schema::table('governorates', function (Blueprint $table) {
            $table->dropIndex('idx_governorates_cancelled');
        });

        // Drop districts indexes
        Schema::table('districts', function (Blueprint $table) {
            $table->dropIndex('idx_districts_cancelled');
            $table->dropIndex('idx_districts_gov_cancelled');
        });

        // Drop zones indexes
        Schema::table('zones', function (Blueprint $table) {
            $table->dropIndex('idx_zones_cancelled');
            $table->dropIndex('idx_zones_district_cancelled');
        });

        // Drop cities indexes
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex('idx_cities_cancelled');
            $table->dropIndex('idx_cities_zone_cancelled');
        });

        // Drop budgets indexes
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('idx_budgets_cancelled');
            $table->dropIndex('idx_budgets_refill_date');
            $table->dropIndex('idx_budgets_zone_cancelled');
        });

        // Drop diaper_budgets indexes
        Schema::table('diaper_budgets', function (Blueprint $table) {
            $table->dropIndex('idx_diaper_budgets_cancelled');
            $table->dropIndex('idx_diaper_budgets_refill_date');
            $table->dropIndex('idx_diaper_budgets_zone_cancelled');
        });

        // Drop public_institutions indexes
        Schema::table('public_institutions', function (Blueprint $table) {
            $table->dropIndex('idx_public_inst_cancelled');
            $table->dropIndex('idx_public_inst_name');
            $table->dropIndex('idx_public_inst_city_cancelled');
        });

        // Drop request_statuses indexes
        Schema::table('request_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_request_statuses_order');
            $table->dropIndex('idx_request_statuses_cancelled');
        });
    }
};
