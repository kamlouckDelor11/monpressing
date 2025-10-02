<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->uuid('client_token');
            $table->uuid('pressing_token');
            $table->uuid('user_token');
            $table->string('reference');
            $table->string('type'); // 'LAVOMATIC' or 'PRESSING'
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending'); // 'pending', 'paid', 'partially_paid'
            $table->string('delivery_status')->default('pending'); // 'pending', 'ready', 'delivered'
            $table->date('deposit_date');
            $table->date('delivery_date');
            $table->timestamps();

            $table->foreign('client_token')->references('token')->on('clients')->onDelete('cascade');
            $table->foreign('pressing_token')->references('pressing_token')->on('users')->onDelete('cascade');
            $table->foreign('user_token')->references('user_token')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};