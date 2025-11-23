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
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->enum('type', ['refill', 'deduction', 'adjustment', 'allocation']);
            $table->integer('amount');
            $table->integer('balance_after');
            $table->unsignedBigInteger('request_id')->nullable();
            $table->foreign('request_id')->references('id')->on('request_headers')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('cancelled')->default(0)->comment('Soft delete flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_transactions');
    }
};
