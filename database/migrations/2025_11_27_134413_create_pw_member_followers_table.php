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
        Schema::create('pw_member_followers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pw_member_id');
            $table->unsignedBigInteger('follower_id');
            $table->timestamps();

            $table->foreign('pw_member_id')->references('id')->on('pw_members')->onDelete('cascade');
            $table->foreign('follower_id')->references('id')->on('pw_members')->onDelete('cascade');

            // Ensure a follower can only be added once to a member
            $table->unique(['pw_member_id', 'follower_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pw_member_followers');
    }
};
