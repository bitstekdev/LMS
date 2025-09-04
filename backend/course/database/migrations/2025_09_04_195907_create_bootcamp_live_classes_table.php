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
        Schema::create('bootcamp_live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('bootcamp_modules')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();
            $table->integer('start_time')->nullable();
            $table->integer('end_time')->nullable();
            $table->integer('sort')->nullable();
            $table->string('status')->nullable();
            $table->string('provider')->nullable();
            $table->longText('joining_data')->nullable();
            $table->boolean('force_stop')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootcamp_live_classes');
    }
};
