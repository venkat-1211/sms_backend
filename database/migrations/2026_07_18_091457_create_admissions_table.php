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
        Schema::create('admissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->date('admission_date');
            $table->decimal('total_fee', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            // $table->decimal('balance_fee', 10, 2)->storedAs('total_fee - amount_paid');
            $table->decimal('balance_fee', 10, 2);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->index(['student_id', 'course_id'], 'idx_student_course');
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['payment_status', 'balance_fee'], 'idx_payment_balance');
            $table->index(['admission_date'], 'idx_admission_date');
            $table->index(['student_id', 'payment_status'], 'idx_student_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
