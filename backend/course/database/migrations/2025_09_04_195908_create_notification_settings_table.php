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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->boolean('is_editable')->nullable();
            $table->string('addon_identifier')->nullable();
            $table->string('user_types')->nullable();
            $table->string('system_notification')->nullable();
            $table->string('email_notification')->nullable();
            $table->string('subject')->nullable();
            $table->longText('template')->nullable();
            $table->string('setting_title')->nullable();
            $table->string('setting_sub_title')->nullable();
            $table->string('date_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
