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
        Schema::create('diapers_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diapers_request_id')->constrained('diapers_requests')->onDelete('cascade');
            $table->string('size'); // XS, S, M, L, XL, XXL, etc.
            $table->integer('count');
            $table->timestamps();

            // Indexes
            $table->index('diapers_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diapers_request_items');
    }
};
