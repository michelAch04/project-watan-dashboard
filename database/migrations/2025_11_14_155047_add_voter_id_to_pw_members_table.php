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
        Schema::table('pw_members', function (Blueprint $table) {
            $table->unsignedBigInteger('voter_id')->nullable()->after('email');
            $table->foreign('voter_id')->references('id')->on('voters_list')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pw_members', function (Blueprint $table) {
            $table->dropForeign(['voter_id']);
            $table->dropColumn('voter_id');
        });
    }
};
