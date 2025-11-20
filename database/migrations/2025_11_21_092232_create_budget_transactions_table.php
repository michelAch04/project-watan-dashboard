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
            $table->enum('type', ['refill', 'deduction', 'adjustment']); // refill = monthly auto-refill, deduction = request allocation, adjustment = manual edit
            $table->integer('amount'); // positive for refill/additions, negative for deductions
            $table->integer('balance_after'); // budget balance after this transaction
            $table->unsignedBigInteger('request_id')->nullable(); // if transaction is from a request allocation
            $table->foreign('request_id')->references('id')->on('request_headers')->onDelete('set null');
            $table->text('description')->nullable(); // description of the transaction
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
