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
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->text('address');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['email', 'status'], 'idx_email_status');
            $table->index(['mobile', 'status'], 'idx_mobile_status');
            $table->index(['full_name'], 'idx_full_name');
            $table->index(['gender', 'status'], 'idx_gender_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
