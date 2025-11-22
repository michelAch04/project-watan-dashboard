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
        Schema::create('diaper_budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diaper_budget_id');
            $table->foreign('diaper_budget_id')->references('id')->on('diaper_budgets')->onDelete('cascade');
            $table->enum('type', ['refill', 'deduction', 'adjustment', 'allocation', 'allocation_processed']); // refill = monthly auto-refill, deduction = request allocation, adjustment = manual edit, allocation = future month allocation
            $table->json('quantity_change'); // {"xl": -20, "l": 10, "m": -30, "s": 5} - positive for additions, negative for deductions
            $table->json('stock_after'); // {"xl": 60, "l": 40, "m": 100, "s": 30} - stock levels after this transaction
            $table->unsignedBigInteger('request_id')->nullable(); // if transaction is from a request allocation
            $table->foreign('request_id')->references('id')->on('request_headers')->onDelete('set null');
            $table->text('description')->nullable(); // description of the transaction
            $table->boolean('cancelled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diaper_budget_transactions');
    }
};
