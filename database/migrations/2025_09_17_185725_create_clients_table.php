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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->string('pressing_token');
            $table->foreign('pressing_token')
                  ->references('token')->on('pressings')
                  ->onDelete('cascade');
            $table->string('user_token');
            $table->foreign('user_token')
                  ->references('token')->on('users')
                  ->onDelete('cascade');
            $table->string('name'); 
            $table->string('phone', 20);
            $table->string('address', 20);
            $table->enum('status', ['classique', 'prestige'])->default('classique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
