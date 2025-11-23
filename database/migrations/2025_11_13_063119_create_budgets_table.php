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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('description', 255);
            $table->integer('monthly_amount_in_usd');
            $table->integer('current_balance')->default(0);
            $table->integer('auto_refill_day')->default(1);
            $table->date('last_refill_date')->nullable();
            $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            $table->unsignedBigInteger('zone_id')->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
