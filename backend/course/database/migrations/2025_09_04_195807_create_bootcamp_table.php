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
        Schema::create('bootcamps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('bootcamp_categories')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->double('price')->nullable();
            $table->boolean('discount_flag')->default(false);
            $table->double('discounted_price')->nullable();
            $table->timestamp('publish_date')->nullable();
            $table->string('thumbnail')->nullable();
            $table->longText('faqs')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('outcomes')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootcamps');
    }
};
