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
        Schema::create('public_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_header_id')->constrained('request_headers')->onDelete('cascade');
            $table->foreignId('public_institution_id')->constrained('public_institutions');
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('budget_id')->nullable()->constrained('budgets');
            $table->text('notes')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('request_header_id');
            $table->index('public_institution_id');
            $table->index('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_requests');
    }
};
