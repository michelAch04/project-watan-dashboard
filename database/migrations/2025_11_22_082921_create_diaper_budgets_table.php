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
        Schema::create('diaper_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('description', 255);
            $table->json('monthly_restock'); // {"xl": 60, "l": 40, "m": 100, "s": 30}
            $table->json('current_stock'); // {"xl": 60, "l": 40, "m": 100, "s": 30}
            $table->integer('auto_refill_day')->default(1); // day of month for auto-refill (1-28)
            $table->date('last_refill_date')->nullable();
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->boolean('cancelled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diaper_budgets');
    }
};
