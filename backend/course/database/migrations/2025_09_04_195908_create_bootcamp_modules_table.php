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
        Schema::create('bootcamp_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bootcamp_id')->nullable()->constrained('bootcamps')->nullOnDelete();
            $table->string('title')->nullable();
            $table->integer('publish_date')->nullable();
            $table->integer('expiry_date')->nullable();
            $table->string('restriction')->nullable();
            $table->integer('sort')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootcamp_modules');
    }
};
