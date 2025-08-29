<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'nom', 'niveau_id', 'session_id', 'etablissement_id',
    ];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }
}
