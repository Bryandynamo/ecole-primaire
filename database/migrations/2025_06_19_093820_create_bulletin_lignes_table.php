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
        Schema::create('bulletin_lignes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bulletin_id');
            $table->unsignedBigInteger('competence_id');
            $table->unsignedBigInteger('sous_competence_id');
            $table->unsignedBigInteger('modalite_id')->nullable();
            $table->float('note')->nullable();
            $table->string('cote', 3)->nullable();
            $table->integer('points_max')->nullable();
            $table->timestamps();
            $table->foreign('bulletin_id')->references('id')->on('bulletins');
            $table->foreign('competence_id')->references('id')->on('competences');
            $table->foreign('sous_competence_id')->references('id')->on('sous_competences');
            $table->foreign('modalite_id')->references('id')->on('modalites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bulletin_lignes');
    }
};
