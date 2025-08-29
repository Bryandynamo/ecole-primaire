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
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('etablissement_id');
            $table->unsignedBigInteger('eleve_id');
            $table->unsignedBigInteger('sous_competence_id');
            $table->unsignedBigInteger('modalite_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('evaluation_id');
            $table->decimal('valeur', 5, 2);
            $table->tinyInteger('trimestre')->nullable();
            $table->timestamps();
            
            $table->index(['etablissement_id', 'session_id', 'classe_id'], 'idx_notes_etab_session_classe');
            $table->index(['etablissement_id', 'evaluation_id'], 'idx_notes_etab_eval');
            $table->index(['eleve_id', 'evaluation_id'], 'idx_notes_eleve_eval');
            $table->index(['session_id', 'classe_id', 'evaluation_id'], 'idx_notes_session_classe_eval');
            $table->index(['etablissement_id', 'session_id', 'classe_id', 'trimestre'], 'idx_notes_etab_session_classe_trim');
            
            $table->index('etablissement_id');
            $table->index('eleve_id');
            $table->index('evaluation_id');
            $table->index('session_id');
            $table->index('classe_id');
            
            $table->foreign('etablissement_id')->references('id')->on('etablissements')->onDelete('cascade');
            $table->foreign('eleve_id')->references('id')->on('eleves')->onDelete('cascade');
            $table->foreign('sous_competence_id')->references('id')->on('sous_competences')->onDelete('cascade');
            $table->foreign('modalite_id')->references('id')->on('modalites')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
};
