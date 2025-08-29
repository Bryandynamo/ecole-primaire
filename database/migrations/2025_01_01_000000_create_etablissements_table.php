<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255);
            $table->string('code', 50)->unique();
            $table->string('adresse', 500)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('directeur', 255)->nullable();
            $table->enum('type', ['primaire', 'secondaire', 'superieur'])->default('secondaire');
            $table->boolean('actif')->default(true);
            $table->json('config')->nullable(); // Configuration spécifique à l'établissement
            $table->timestamps();
            
            // Index pour les recherches
            $table->index('code');
            $table->index('type');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etablissements');
    }
}; 