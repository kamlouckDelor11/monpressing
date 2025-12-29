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
        Schema::create('goals', function (Blueprint $table) {
            // Clé Primaire de Type UUID (TOKEN)
            $table->uuid('token')->primary(); 
            
            // Clés Étrangères (doivent être des string pour correspondre aux tokens)
            $table->string('pressing_token'); 
            $table->string('user_token')->nullable(); // Utilisateur ciblé

            // Type d'objectif
            $table->enum('type', ['deposits', 'revenue', 'deliveries', 'new_clients', 'charges']);

            // Périodicité
            $table->enum('periodicity', ['monthly', 'quarterly', 'annual']);
            
            // Date de début et de fin de l'objectif
            $table->date('start_date');
            $table->date('end_date');
            
            // Valeur cible de l'objectif
            $table->decimal('target_value', 10, 2); 

            $table->timestamps();

            // Index pour la recherche rapide
            $table->index('pressing_token');
            $table->index('user_token'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};