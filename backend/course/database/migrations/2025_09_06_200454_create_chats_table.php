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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('reciver_id');
            $table->unsignedBigInteger('message_thrade');
            $table->text('chatcenter')->nullable();
            $table->text('message')->nullable();
            $table->boolean('file')->default(false);
            $table->string('reaction')->nullable();
            $table->tinyInteger('read_status')->default(0);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reciver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('message_thrade')->references('id')->on('message_threads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
