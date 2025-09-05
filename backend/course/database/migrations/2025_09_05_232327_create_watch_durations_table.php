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
        Schema::create('watch_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watched_student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('watched_course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('watched_lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->integer('current_duration')->nullable();
            $table->longText('watched_counter')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_durations');
    }
};
