<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
    public function sousCompetence()
    {
        return $this->belongsTo(SousCompetence::class);
    }
    public function modalite()
    {
        return $this->belongsTo(Modalite::class);
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
    // Champ legacy 'trimestre' remplacé par 'evaluation_id' (FK vers evaluations)

    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'sous_competence_id',
        'modalite_id',
        'session_id',
        'classe_id',
        'evaluation_id', // nouveau
        'valeur',
    ];
}
