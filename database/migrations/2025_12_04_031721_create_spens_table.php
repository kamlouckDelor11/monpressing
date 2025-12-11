<?php

// database/migrations/..._create_spens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spens', function (Blueprint $table) {
            $table->uuid('token')->primary(); // PK
            $table->uuid('user_token'); // Qui a créé la catégorie
            $table->uuid('pressing_token'); // Clé de filtrage
            
            $table->string('description');
            $table->enum('nature', ['fixed', 'variable'])->default('variable');
            $table->decimal('default_amount', 10, 2)->nullable(); // Montant si nature='fixed'
            $table->timestamps();

            // Clés étrangères
            $table->foreign('user_token')->references('token')->on('users')->onDelete('cascade');
            $table->foreign('pressing_token')->references('token')->on('pressings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spens');
    }
};
