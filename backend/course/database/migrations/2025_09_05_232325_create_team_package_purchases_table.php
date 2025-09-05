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
        Schema::create('team_package_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('team_training_packages')->nullOnDelete();
            $table->double('price')->nullable();
            $table->double('admin_revenue')->nullable();
            $table->double('instructor_revenue')->nullable();
            $table->double('tax')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('payment_details')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_package_purchases');
    }
};
