<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'enseignant_id',
        'date_eval',
        'trimestre',
        'session_id',
        'classe_id',
        'numero_eval',
    ];

    protected $casts = [
        'date_eval' => 'date',
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function lecons()
    {
        return $this->belongsToMany(Lecon::class, 'lecon_evaluation')
            ->withPivot('total_a_couvrir_ua')
            ->withTimestamps();
    }
}
