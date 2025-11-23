<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Phase 2: Transaction Table Indexes
     * - budget_transactions: High transaction volume
     * - diaper_budget_transactions: High transaction volume
     * - monthly_lists: Monthly reporting queries
     */
    public function up(): void
    {
        // budget_transactions - HIGH: Transaction history and reports
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->index('type', 'idx_budget_trans_type');
            $table->index('created_at', 'idx_budget_trans_created');
            $table->index('cancelled', 'idx_budget_trans_cancelled');
            $table->index(['budget_id', 'type'], 'idx_budget_trans_budget_type');
            $table->index(['budget_id', 'created_at'], 'idx_budget_trans_budget_created');
            $table->index(['request_id', 'type'], 'idx_budget_trans_request_type');
        });

        // diaper_budget_transactions - HIGH: Diaper transaction history
        Schema::table('diaper_budget_transactions', function (Blueprint $table) {
            $table->index('type', 'idx_diaper_trans_type');
            $table->index('created_at', 'idx_diaper_trans_created');
            $table->index('cancelled', 'idx_diaper_trans_cancelled');
            $table->index(['diaper_budget_id', 'type'], 'idx_diaper_trans_budget_type');
            $table->index(['diaper_budget_id', 'created_at'], 'idx_diaper_trans_budget_created');
            $table->index(['diaper_budget_id', 'cancelled'], 'idx_diaper_trans_budget_cancelled');
        });

        // monthly_lists - HIGH: Monthly reporting and filtering
        Schema::table('monthly_lists', function (Blueprint $table) {
            $table->index('month', 'idx_monthly_lists_month');
            $table->index('year', 'idx_monthly_lists_year');
            $table->index('cancelled', 'idx_monthly_lists_cancelled');
            $table->index(['user_id', 'month', 'year'], 'idx_monthly_lists_user_period');
            $table->index(['month', 'year'], 'idx_monthly_lists_period');
            $table->index(['user_id', 'cancelled'], 'idx_monthly_lists_user_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop budget_transactions indexes
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_budget_trans_type');
            $table->dropIndex('idx_budget_trans_created');
            $table->dropIndex('idx_budget_trans_cancelled');
            $table->dropIndex('idx_budget_trans_budget_type');
            $table->dropIndex('idx_budget_trans_budget_created');
            $table->dropIndex('idx_budget_trans_request_type');
        });

        // Drop diaper_budget_transactions indexes
        Schema::table('diaper_budget_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_diaper_trans_type');
            $table->dropIndex('idx_diaper_trans_created');
            $table->dropIndex('idx_diaper_trans_cancelled');
            $table->dropIndex('idx_diaper_trans_budget_type');
            $table->dropIndex('idx_diaper_trans_budget_created');
            $table->dropIndex('idx_diaper_trans_budget_cancelled');
        });

        // Drop monthly_lists indexes
        Schema::table('monthly_lists', function (Blueprint $table) {
            $table->dropIndex('idx_monthly_lists_month');
            $table->dropIndex('idx_monthly_lists_year');
            $table->dropIndex('idx_monthly_lists_cancelled');
            $table->dropIndex('idx_monthly_lists_user_period');
            $table->dropIndex('idx_monthly_lists_period');
            $table->dropIndex('idx_monthly_lists_user_cancelled');
        });
    }
};
