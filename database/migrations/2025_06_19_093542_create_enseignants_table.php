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
        Schema::create('enseignants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 50);
            $table->string('prenom', 50);
            $table->string('matricule', 20);
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('session_id');
            $table->timestamps();
            $table->foreign('classe_id')->references('id')->on('classes');
            $table->foreign('session_id')->references('id')->on('sessions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enseignants');
    }
};
