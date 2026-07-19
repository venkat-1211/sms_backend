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
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->string('course_name');
            $table->integer('duration');
            $table->decimal('total_fee', 10, 2);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['course_name', 'status'], 'idx_name_status');
            $table->index(['duration', 'status'], 'idx_duration_status');
            $table->index(['total_fee'], 'idx_total_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
