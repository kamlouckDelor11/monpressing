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
        Schema::create('pressings', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->string('name'); // nom du pressing
            $table->string('phone', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('subscription_plan')->default('basic');
            $table->date('last_subscription_at')->nullable();
            $table->date('subscription_expires_at')->nullable();
            $table->string('logo')->nullable();
            $table->string('niu')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pressings');
    }
};
