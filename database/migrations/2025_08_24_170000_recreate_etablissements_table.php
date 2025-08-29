<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Créer la table uniquement si elle n'existe pas déjà (ne pas la supprimer pour éviter les erreurs de FK)
        if (!Schema::hasTable('etablissements')) {
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
                $table->json('config')->nullable();
                $table->timestamps();
                
                // Index pour les recherches
                $table->index('code');
                $table->index('type');
                $table->index('actif');
            });
        }
    }

    public function down()
    {
        // Ne rien faire en down pour éviter la perte de données involontaire
    }
};
