<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lecons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sous_competence_id');
            $table->string('nom');
            $table->integer('total_a_couvrir_annee')->nullable();
            $table->timestamps();

            $table->foreign('sous_competence_id')->references('id')->on('sous_competences')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lecons');
    }
};
