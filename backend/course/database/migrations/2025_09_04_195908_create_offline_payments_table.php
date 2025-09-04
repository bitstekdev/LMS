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
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('item_type')->nullable();
            $table->string('items')->nullable();
            $table->double('tax')->nullable();
            $table->double('total_amount')->nullable();
            $table->string('coupon')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('bank_no')->nullable();
            $table->string('doc')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_payments');
    }
};
