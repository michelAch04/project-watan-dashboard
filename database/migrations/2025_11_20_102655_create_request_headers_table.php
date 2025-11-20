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
        Schema::create('request_headers', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->date('request_date');
            $table->foreignId('request_status_id')->constrained('request_statuses');
            $table->foreignId('reference_member_id')->nullable()->constrained('pw_members');
            $table->date('ready_date')->nullable();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('current_user_id')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->integer('published_count')->default(0);
            $table->boolean('cancelled')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index('request_number');
            $table->index('request_date');
            $table->index(['sender_id', 'request_status_id']);
            $table->index(['current_user_id', 'request_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_headers');
    }
};
