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
        Schema::create('bootcamp_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bootcamp_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->text('payment_details')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->decimal('admin_revenue', 10, 2)->nullable();
            $table->decimal('instructor_revenue', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['bootcamp_id', 'user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootcamp_purchases');
    }
};
