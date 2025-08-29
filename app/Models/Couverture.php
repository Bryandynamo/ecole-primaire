<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Couverture extends Model
{
    use HasFactory;
    protected $fillable = [
        'lecon_id',
        'sous_competence_id',
        'evaluation_id',
        'classe_id',
        'nb_couverts',
    ];

    public function lecon()
    {
        return $this->belongsTo(Lecon::class);
    }

    public function sousCompetence()
    {
        return $this->belongsTo(SousCompetence::class, 'sous_competence_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}
