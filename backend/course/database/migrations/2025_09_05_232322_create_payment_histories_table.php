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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('payment_type', 50)->nullable();
            $table->double('amount')->nullable();
            $table->string('invoice')->nullable();
            $table->unsignedInteger('date_added')->nullable();
            $table->unsignedInteger('last_modified')->nullable();
            $table->string('admin_revenue')->nullable();
            $table->string('instructor_revenue')->nullable();
            $table->double('tax')->nullable();
            $table->boolean('instructor_payment_status')->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('coupon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
