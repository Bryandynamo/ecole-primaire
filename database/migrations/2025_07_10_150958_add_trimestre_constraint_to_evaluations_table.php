<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Utilisation de SQL brut pour éviter la dépendance à Doctrine/DBAL
        DB::statement('ALTER TABLE evaluations MODIFY trimestre TINYINT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revenir à l'état précédent en utilisant également du SQL brut
        DB::statement('ALTER TABLE evaluations MODIFY trimestre INT NULL');
    }
};
