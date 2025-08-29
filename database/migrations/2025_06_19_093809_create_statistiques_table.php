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
        Schema::create('statistiques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('classe_id');
            $table->integer('nb_inscrits');
            $table->integer('nb_evalues');
            $table->integer('nb_admis');
            $table->integer('nb_echoues');
            $table->float('pct_filles')->nullable();
            $table->float('pct_garcons')->nullable();
            $table->date('date_generation')->nullable();
            $table->timestamps();
            $table->foreign('evaluation_id')->references('id')->on('evaluations');
            $table->foreign('classe_id')->references('id')->on('classes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statistiques');
    }
};
