<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecon extends Model
{
    use HasFactory;
    protected $fillable = [
        'sous_competence_id',
        'nom',
        'total_a_couvrir_annee',
        'total_a_couvrir_trimestre',
    ];

    public function sousCompetence()
    {
        return $this->belongsTo(SousCompetence::class, 'sous_competence_id');
    }

    public function couvertures()
    {
        return $this->hasMany(Couverture::class);
    }

    public function evaluations()
    {
        return $this->belongsToMany(Evaluation::class, 'lecon_evaluation')
            ->withPivot('total_a_couvrir_ua')
            ->withTimestamps();
    }
}
