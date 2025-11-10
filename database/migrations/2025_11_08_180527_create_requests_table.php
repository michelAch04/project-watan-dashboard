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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->date('request_date');
            
            // Type and Status
            $table->unsignedBigInteger('request_type_id');
            $table->unsignedBigInteger('request_status_id');
            
            // Requester Information (for humanitarian, diapers, others)
            $table->string('requester_first_name')->nullable();
            $table->string('requester_father_name')->nullable();
            $table->string('requester_last_name')->nullable();
            $table->unsignedBigInteger('requester_city_id')->nullable();
            $table->string('requester_ro_number')->nullable()->comment('رقم السجل');
            $table->string('requester_phone')->nullable();
            $table->unsignedBigInteger('voter_id')->nullable()->comment('Linked voter from voters_list');
            
            // Public Institution (for public requests)
            $table->unsignedBigInteger('public_institution_id')->nullable();
            
            // Request Details
            $table->string('subtype')->nullable()->comment('تربوية, طبية, استشفائية, إجتماعية, etc.');
            $table->unsignedBigInteger('reference_member_id')->nullable()->comment('PW Member reference');
            $table->decimal('amount', 10, 2)->nullable()->comment('Amount in USD');
            $table->text('notes')->nullable();
            
            // Workflow
            $table->unsignedBigInteger('sender_id')->comment('User who created the request');
            $table->unsignedBigInteger('current_user_id')->nullable()->comment('Current user handling the request');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('request_type_id')->references('id')->on('request_types')->onDelete('cascade');
            $table->foreign('request_status_id')->references('id')->on('request_statuses')->onDelete('cascade');
            $table->foreign('requester_city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('voter_id')->references('id')->on('voters_list')->onDelete('set null');
            $table->foreign('public_institution_id')->references('id')->on('public_institutions')->onDelete('set null');
            $table->foreign('reference_member_id')->references('id')->on('pw_members')->onDelete('set null');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
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
        Schema::dropIfExists('requests');
    }
};