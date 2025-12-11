<?php

// database/migrations/..._create_item_spens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_spens', function (Blueprint $table) {
            $table->uuid('token')->primary(); // PK
            $table->uuid('spens_token'); // FK vers la Catégorie (Spens)
            $table->uuid('user_token'); // Qui a enregistré la dépense
            $table->uuid('pressing_token'); // Clé de filtrage
            
            $table->string('description')->nullable();
            $table->decimal('amount_spens', 10, 2);
            $table->date('date_spens');
            $table->string('payment_mode', 50);
            $table->enum('status', ['validated', 'canceled'])->default('validated');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('spens_token')
                ->references('token')
                ->on('spens')
                ->onDelete('cascade');

            $table->foreign('user_token')
                ->references('token')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('pressing_token')
                ->references('token')
                ->on('pressings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_spens');
    }
};
