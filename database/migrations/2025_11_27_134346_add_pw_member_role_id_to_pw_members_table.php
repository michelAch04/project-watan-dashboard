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
            $table->unsignedBigInteger('pw_member_role_id')->nullable()->after('voter_id');
            $table->foreign('pw_member_role_id')->references('id')->on('pw_member_roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pw_members', function (Blueprint $table) {
            $table->dropForeign(['pw_member_role_id']);
            $table->dropColumn('pw_member_role_id');
        });
    }
};
