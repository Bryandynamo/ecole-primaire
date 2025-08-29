<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'description',
        'niveau_id',
    ];

    /**
     * Get the sous-competences for the competence.
     */
    public function sousCompetences()
    {
        return $this->hasMany(SousCompetence::class);
    }

    /**
     * The evaluations that belong to the competence.
     */
    public function evaluations()
    {
        return $this->belongsToMany(Evaluation::class, 'competence_evaluation');
    }

    /**
     * Get the niveau that owns the competence.
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    /**
     * Get the session that owns the competence.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}