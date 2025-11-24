<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if user_id column exists
        if (!Schema::hasColumn('push_subscriptions', 'user_id')) {
            throw new \Exception('push_subscriptions table does not have user_id column. Migration cannot proceed.');
        }

        // Drop foreign key if it exists
        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, continue
        }

        // Drop index if it exists
        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Rename user_id to subscribable_id
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'subscribable_id');
        });

        // Add the subscribable_type column
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('subscribable_type')->after('id');
            $table->index(['subscribable_type', 'subscribable_id']);
        });

        // Update existing records to set subscribable_type
        DB::table('push_subscriptions')->update([
            'subscribable_type' => 'App\\Models\\User'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscribable_type', 'subscribable_id']);
            $table->dropColumn('subscribable_type');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->renameColumn('subscribable_id', 'user_id');
        });

        // Try to recreate foreign key and index
        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        } catch (\Exception $e) {
            // Could not recreate foreign key/index
        }
    }
};
