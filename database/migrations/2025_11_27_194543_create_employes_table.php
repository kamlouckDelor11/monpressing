<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->uuid('token')->primary(); // Clé primaire (UUID)
            $table->string('full_name');
            $table->string('function');
            $table->decimal('base_salary', 10, 2);
            $table->date('hiring_date'); // Date d'embauche
            

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
        Schema::dropIfExists('employes');
    }
};