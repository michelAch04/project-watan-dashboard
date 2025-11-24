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
        Schema::create('pw_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 191);
            $table->string('father_name', 191);
            $table->string('last_name', 191);
            $table->string('mother_full_name', 191);
            $table->string('phone', 191);
            $table->string('email', 191)->nullable();
            $table->unsignedBigInteger('voter_id')->nullable();
            $table->string('office_status')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('cancelled')->default(0);
            $table->timestamps();

            $table->foreign('voter_id')->references('id')->on('voters_list')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pw_members');
    }
};