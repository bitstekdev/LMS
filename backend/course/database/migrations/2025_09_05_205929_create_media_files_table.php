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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('post_id')->nullable();
            $table->unsignedInteger('story_id')->nullable();
            $table->unsignedInteger('album_id')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('page_id')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('chat_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->string('privacy', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
