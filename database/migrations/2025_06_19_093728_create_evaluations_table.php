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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enseignant_id');
            $table->date('date_eval')->nullable();
            $table->enum('trimestre', ['T1','T2','T3']);
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('classe_id');
            $table->integer('numero_eval')->nullable();
            $table->timestamps();
            $table->foreign('enseignant_id')->references('id')->on('enseignants');
            $table->foreign('session_id')->references('id')->on('sessions');
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
        Schema::dropIfExists('evaluations');
    }
};
