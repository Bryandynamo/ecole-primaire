<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'prenom', 'matricule', 'sexe', 'date_naissance', 'classe_id', 'session_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($eleve) {
            if (empty($eleve->matricule)) {
                $last = self::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $eleve->matricule = 'ELV' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }

    // Accesseur pour obtenir le nom complet de l'élève
    public function getNomCompletAttribute()
    {
        return trim($this->nom . ' ' . $this->prenom);
    }
}
