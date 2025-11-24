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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->uuid('order_token')->nullable();
            $table->uuid('user_token');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('description');
            $table->string('type');
            $table->date('payment_date');
            $table->timestamps();

            $table->foreign('order_token')->references('token')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
