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
        Schema::create('bulletins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eleve_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('classe_id');
            $table->enum('trimestre', ['T1','T2','T3']);
            $table->date('date_generation')->nullable();
            $table->string('pdf_url', 255)->nullable();
            $table->timestamps();
            $table->foreign('eleve_id')->references('id')->on('eleves');
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
        Schema::dropIfExists('bulletins');
    }
};
