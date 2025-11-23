<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Phase 6: Queue Optimization Indexes
     * - jobs: Queue worker performance
     */
    public function up(): void
    {
        // jobs - LOW: Queue processing optimization
        Schema::table('jobs', function (Blueprint $table) {
            $table->index('available_at', 'idx_jobs_available_at');
            $table->index(['queue', 'available_at'], 'idx_jobs_queue_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop jobs indexes
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('idx_jobs_available_at');
            $table->dropIndex('idx_jobs_queue_available');
        });
    }
};
