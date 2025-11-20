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
        // Add cancelled field to request statuses table
        if (!Schema::hasColumn('request_statuses', 'cancelled')) {
            Schema::table('request_statuses', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('order')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to budgets table
        if (!Schema::hasColumn('budgets', 'cancelled')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('last_refill_date')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to budget_transactions table
        if (!Schema::hasColumn('budget_transactions', 'cancelled')) {
            Schema::table('budget_transactions', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('description')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to monthly_lists table
        if (!Schema::hasColumn('monthly_lists', 'cancelled')) {
            Schema::table('monthly_lists', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('year')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to users table
        if (!Schema::hasColumn('users', 'cancelled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('remember_token')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to pw_members table
        if (!Schema::hasColumn('pw_members', 'cancelled')) {
            Schema::table('pw_members', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('status')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to voters_list table
        if (!Schema::hasColumn('voters_list', 'cancelled')) {
            Schema::table('voters_list', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('notes')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to public_institutions table
        if (!Schema::hasColumn('public_institutions', 'cancelled')) {
            Schema::table('public_institutions', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('notes')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to zones table
        if (!Schema::hasColumn('zones', 'cancelled')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('user_id')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to cities table
        if (!Schema::hasColumn('cities', 'cancelled')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('zone_id')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to districts table
        if (!Schema::hasColumn('districts', 'cancelled')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('governorate_id')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to governorates table
        if (!Schema::hasColumn('governorates', 'cancelled')) {
            Schema::table('governorates', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('name_ar')->comment('Soft delete flag');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_statuses', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('monthly_lists', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('pw_members', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('voters_list', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('public_institutions', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::table('governorates', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });
    }
};
