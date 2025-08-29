<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'code', 'adresse', 'telephone',
    ];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function enseignants()
    {
        return $this->hasMany(Enseignant::class);
    }
}
