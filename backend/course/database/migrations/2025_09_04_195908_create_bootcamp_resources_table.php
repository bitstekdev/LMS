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
        Schema::create('bootcamp_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('bootcamp_modules')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('upload_type')->nullable();
            $table->string('file')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootcamp_resources');
    }
};
