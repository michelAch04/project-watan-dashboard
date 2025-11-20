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
        Schema::create('diapers_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_header_id')->constrained('request_headers')->onDelete('cascade');
            $table->foreignId('voter_id')->constrained('voters_list');
            $table->foreignId('budget_id')->nullable()->constrained('budgets');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('request_header_id');
            $table->index('voter_id');
            $table->index('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diapers_requests');
    }
};
