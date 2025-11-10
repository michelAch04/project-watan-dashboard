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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('pw_member_id')->nullable()->after('id');
            $table->foreign('pw_member_id')->references('id')->on('pw_members')->onDelete('cascade');
            $table->unique('pw_member_id'); // One user per PW member
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pw_member_id']);
            $table->dropColumn('pw_member_id');
        });
    }
};