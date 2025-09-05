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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('short_description')->nullable();
            $table->string('course_type')->nullable();
            $table->string('status')->nullable();
            $table->string('level')->nullable();
            $table->string('language')->nullable();
            $table->boolean('is_paid')->nullable();
            $table->boolean('is_best')->default(false);
            $table->double('price')->nullable();
            $table->double('discounted_price')->nullable();
            $table->boolean('discount_flag')->nullable();
            $table->boolean('enable_drip_content')->nullable();
            $table->longText('drip_content_settings')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->string('preview')->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('requirements')->nullable();
            $table->mediumText('outcomes')->nullable();
            $table->mediumText('faqs')->nullable();
            $table->text('instructor_ids')->nullable();
            $table->integer('average_rating')->default(0);
            $table->integer('expiry_period')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
