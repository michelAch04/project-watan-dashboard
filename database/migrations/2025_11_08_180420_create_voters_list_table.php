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
        Schema::create('voters_list', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 191);
            $table->string('father_name', 191);
            $table->string('last_name', 191);
            $table->string('mother_full_name', 191);
            $table->unsignedBigInteger('city_id');
            $table->string('register_number', 191)->comment('رقم السجل');
            $table->string('phone', 191)->nullable();
            $table->boolean('cancelled')->default(0);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->index('register_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voters_list');
    }
};