<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousCompetence extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'competence_id', 'nom', 'points_max',
    ];

    public function competence()
    {
        return $this->belongsTo(Competence::class);
    }

    public function modalites()
    {
        // Correction : le nom réel de la table pivot est 'sous_competence_modalites'
        return $this->belongsToMany(Modalite::class, 'sous_competence_modalites');
    }
}
