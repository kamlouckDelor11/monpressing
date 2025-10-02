<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->uuid('order_token');
            $table->uuid('service_token')->nullable();
            $table->uuid('article_token')->nullable();
            $table->string('item_name');
            $table->string('item_type'); // 'lavomatic' or 'pressing_service'
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->foreign('order_token')->references('token')->on('orders')->onDelete('cascade');
            $table->foreign('service_token')->references('token')->on('services')->onDelete('set null');
            $table->foreign('article_token')->references('token')->on('articles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};