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
        // First, convert existing string values to JSON arrays
        DB::table('budgets')->get()->each(function ($budget) {
            $requestTypes = $budget->request_type ? [$budget->request_type] : [];
            DB::table('budgets')
                ->where('id', $budget->id)
                ->update(['request_type' => json_encode($requestTypes)]);
        });

        // Then change the column type to JSON
        Schema::table('budgets', function (Blueprint $table) {
            $table->json('request_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON arrays back to single string (take first value)
        DB::table('budgets')->get()->each(function ($budget) {
            $requestTypes = json_decode($budget->request_type, true);
            $singleType = is_array($requestTypes) && count($requestTypes) > 0 ? $requestTypes[0] : null;
            DB::table('budgets')
                ->where('id', $budget->id)
                ->update(['request_type' => $singleType]);
        });

        // Change column back to string
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('request_type')->nullable()->change();
        });
    }
};
