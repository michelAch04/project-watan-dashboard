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
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_id')->nullable()->after('amount');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('set null');
            $table->date('ready_date')->nullable()->after('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn(['budget_id', 'ready_date']);
        });
    }
};
