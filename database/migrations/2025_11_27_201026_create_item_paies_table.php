<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_paies', function (Blueprint $table) {
            $table->uuid('token')->primary(); // Clé primaire (UUID)
            
            // Montants détaillés (peuvent être null)
            $table->decimal('base_salary', 10, 2);
            $table->decimal('advantages', 10, 2)->nullable();
            $table->decimal('prime', 10, 2)->nullable();
            $table->decimal('fiscal_retention', 10, 2)->nullable();
            $table->decimal('social_retention', 10, 2)->nullable();
            $table->decimal('patronal_contribution', 10, 2)->nullable();
            $table->decimal('fiscal_charge', 10, 2)->nullable();
            $table->decimal('exceptional_retention', 10, 2)->nullable();
            $table->decimal('net_paid', 10, 2); // Net payé à l'employé



            $table->foreignUuid('pressing_token')
                ->constrained('pressings', 'token')
                ->onDelete('cascade');

            $table->foreignUuid('user_token')
                ->constrained('users', 'token')
                ->onDelete('cascade');

            $table->foreignUuid('paie_token')
                ->constrained('paies', 'token')
                ->onDelete('cascade');

            $table->foreignUuid('employe_token')
                ->constrained('employes', 'token')
                ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_paies');
    }
};