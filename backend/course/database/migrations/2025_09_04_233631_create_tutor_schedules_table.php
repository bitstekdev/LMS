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
        Schema::create('tutor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('tutor_categories')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('tutor_subjects')->nullOnDelete();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->integer('tution_type')->nullable();
            $table->integer('duration')->nullable();
            $table->longText('description')->nullable();
            $table->integer('status')->default(0);
            $table->foreignId('booking_id')->nullable()->constrained('tutor_bookings')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_schedules');
    }
};
