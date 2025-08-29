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
        Schema::create('competences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('niveau_id');
            $table->integer('points_max');
            $table->unsignedBigInteger('session_id');
            $table->timestamps();
            $table->foreign('niveau_id')->references('id')->on('niveaux');
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
        Schema::dropIfExists('competences');
    }
};
