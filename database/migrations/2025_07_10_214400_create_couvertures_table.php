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
        Schema::create('couvertures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lecon_id')->nullable();
            $table->unsignedBigInteger('sous_competence_id')->nullable();
            $table->unsignedBigInteger('evaluation_id')->nullable();
            $table->unsignedBigInteger('classe_id')->nullable();
            $table->integer('nb_couverts')->nullable();
            $table->timestamps();

            $table->foreign('lecon_id')->references('id')->on('lecons')->onDelete('cascade');
            $table->foreign('sous_competence_id')->references('id')->on('sous_competences')->onDelete('cascade');
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
            $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('couvertures');
    }
};
