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
        Schema::table('budgets', function (Blueprint $table) {
            $table->integer('current_balance')->default(0)->after('monthly_amount_in_usd');
            $table->integer('auto_refill_day')->default(1)->after('current_balance'); // day of month for auto-refill (1-28)
            $table->date('last_refill_date')->nullable()->after('auto_refill_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn(['current_balance', 'auto_refill_day', 'last_refill_date']);
        });
    }
};
