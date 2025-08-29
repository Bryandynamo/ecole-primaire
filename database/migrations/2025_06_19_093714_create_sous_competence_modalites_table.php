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
        Schema::create('sous_competence_modalites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sous_competence_id');
            $table->unsignedBigInteger('modalite_id');
            $table->integer('points_max');
            $table->timestamps();
            $table->foreign('sous_competence_id')->references('id')->on('sous_competences');
            $table->foreign('modalite_id')->references('id')->on('modalites');
            $table->unique(['sous_competence_id', 'modalite_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sous_competence_modalites');
    }
};
