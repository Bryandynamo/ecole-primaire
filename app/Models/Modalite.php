<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalite extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'nom',
    ];

    public function sousCompetences()
    {
        return $this->belongsToMany(SousCompetence::class, 'sous_competence_modalites')
            ->withPivot('points_max')
            ->withTimestamps();
    }
}
