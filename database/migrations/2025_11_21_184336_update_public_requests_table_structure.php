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
        Schema::table('public_requests', function (Blueprint $table) {
            // Drop old public_institution_id column
            $table->dropForeign(['public_institution_id']);
            $table->dropColumn('public_institution_id');

            // Add new columns for public facilities requests
            $table->foreignId('city_id')->after('request_header_id')->constrained('cities');
            $table->text('description')->after('city_id');
            $table->string('requester_full_name')->after('description');
            $table->string('requester_phone')->nullable()->after('requester_full_name');

            // Add index for city_id
            $table->index('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            // Restore public_institution_id
            $table->foreignId('public_institution_id')->after('request_header_id')->constrained('public_institutions');
            $table->index('public_institution_id');

            // Drop new columns
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id', 'description', 'requester_full_name', 'requester_phone']);
        });
    }
};
