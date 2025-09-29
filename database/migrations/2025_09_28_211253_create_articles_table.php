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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->uuid('pressing_token');
            $table->uuid('user_token');
            $table->string('name');
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('pressing_token')
                ->references('pressing_token')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('user_token')
                ->references('user_token')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};