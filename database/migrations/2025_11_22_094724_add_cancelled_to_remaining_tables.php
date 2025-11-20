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
        // Add cancelled field to users table
        if (!Schema::hasColumn('users', 'cancelled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->after('remember_token')->comment('Soft delete flag');
            });
        }

        // Add cancelled field to pw_members table
        if (!Schema::hasColumn('pw_members', 'cancelled')) {
            Schema::table('pw_members', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to voters_list table
        if (!Schema::hasColumn('voters_list', 'cancelled')) {
            Schema::table('voters_list', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to public_institutions table
        if (!Schema::hasColumn('public_institutions', 'cancelled')) {
            Schema::table('public_institutions', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to zones table
        if (!Schema::hasColumn('zones', 'cancelled')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to cities table
        if (!Schema::hasColumn('cities', 'cancelled')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to districts table
        if (!Schema::hasColumn('districts', 'cancelled')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }

        // Add cancelled field to governorates table
        if (!Schema::hasColumn('governorates', 'cancelled')) {
            Schema::table('governorates', function (Blueprint $table) {
                $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'cancelled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('pw_members', 'cancelled')) {
            Schema::table('pw_members', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('voters_list', 'cancelled')) {
            Schema::table('voters_list', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('public_institutions', 'cancelled')) {
            Schema::table('public_institutions', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('zones', 'cancelled')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('cities', 'cancelled')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('districts', 'cancelled')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }

        if (Schema::hasColumn('governorates', 'cancelled')) {
            Schema::table('governorates', function (Blueprint $table) {
                $table->dropColumn('cancelled');
            });
        }
    }
};
