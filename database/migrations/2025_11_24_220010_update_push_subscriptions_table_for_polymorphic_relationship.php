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
        Schema::table('push_subscriptions', function (Blueprint $table) {
            // Drop the old foreign key and index
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);

            // Rename user_id to subscribable_id
            $table->renameColumn('user_id', 'subscribable_id');
        });

        // Add the subscribable_type column and set default value
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
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }
};
