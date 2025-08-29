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
        Schema::create('sous_competences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('competence_id');
            $table->string('nom', 100);
            $table->integer('points_max');
            $table->timestamps();
            $table->foreign('competence_id')->references('id')->on('competences');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sous_competences');
    }
};
