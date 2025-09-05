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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('currency', 10)->nullable();
            $table->string('title')->nullable();
            $table->string('model_name')->nullable();
            $table->text('description')->nullable();
            $table->json('keys')->nullable();
            $table->boolean('status')->default(0);
            $table->boolean('test_mode')->default(1);
            $table->boolean('is_addon')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
