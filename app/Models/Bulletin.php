<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'session_id',
        'classe_id',
        'trimestre',
        'date_generation',
        'pdf_url'
    ];

    protected $with = ['eleve']; // Charge automatiquement les informations de l'élève

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function bulletinLignes()
    {
        return $this->hasMany(BulletinLigne::class);
    }
}
