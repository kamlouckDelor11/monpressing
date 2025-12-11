<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paies', function (Blueprint $table) {
            $table->uuid('token')->primary(); // Clé primaire (UUID)
            
            // Période
            $table->integer('month');
            $table->integer('year');
            
            // Totaux Agrégés
            $table->decimal('total_base_salary', 12, 2);
            $table->decimal('total_advantages', 12, 2);
            $table->decimal('total_primes', 12, 2);
            $table->decimal('total_fiscal_retentions', 12, 2);
            $table->decimal('total_social_retentions', 12, 2);
            $table->decimal('total_patronal_contributions', 12, 2);
            $table->decimal('total_fiscal_charges', 12, 2);
            $table->decimal('total_exceptional_retention', 12, 2);
            $table->decimal('net_to_pay', 12, 2); // Calculé
            
            $table->enum('status', ['pending', 'paid'])->default('pending'); // Statut de paiement

            $table->foreignUuid('pressing_token')
                ->constrained('pressings', 'token')
                ->onDelete('cascade');
            $table->foreignUuid('user_token')
                ->constrained('users', 'token')
                ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paies');
    }
};