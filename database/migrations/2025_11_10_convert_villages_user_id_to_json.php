<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('villages', function (Blueprint $table) {
        //     // Drop the foreign key constraint first
        //     $table->dropForeign(['user_id']);
        //     // Change column type to json
        //     $table->json('user_id')->nullable()->change();
        // });

        // Migrate existing single user_id values to JSON array format
        // $villages = DB::table('villages')->whereNotNull('user_id')->get();
        // foreach ($villages as $village) {
        //     DB::table('villages')
        //         ->where('id', $village->id)
        //         ->update(['user_id' => json_encode([(int)$village->user_id])]);
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            // Revert back to integer with foreign key
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
