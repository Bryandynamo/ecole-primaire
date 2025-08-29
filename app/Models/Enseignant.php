<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'nom', 'prenom', 'matricule', 'classe_id', 'session_id', 'user_id', 'etablissement_id',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }
}
