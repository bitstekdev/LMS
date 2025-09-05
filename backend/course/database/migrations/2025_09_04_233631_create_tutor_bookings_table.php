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
        Schema::create('tutor_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->nullable();
            $table->bigInteger('schedule_id')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->longText('joining_data')->nullable();
            $table->double('price')->nullable();
            $table->double('admin_revenue')->nullable();
            $table->double('instructor_revenue')->nullable();
            $table->double('tax')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('payment_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_bookings');
    }
};
