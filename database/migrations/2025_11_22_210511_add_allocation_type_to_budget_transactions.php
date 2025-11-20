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
        // Update the enum to include 'allocation' type
        DB::statement("ALTER TABLE budget_transactions MODIFY COLUMN type ENUM('refill', 'deduction', 'adjustment', 'allocation')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE budget_transactions MODIFY COLUMN type ENUM('refill', 'deduction', 'adjustment')");
    }
};
