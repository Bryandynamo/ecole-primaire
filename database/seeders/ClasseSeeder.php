<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;
use App\Models\Session;
use App\Models\Niveau;

class ClasseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer une session
        $session = Session::create([
            'nom' => '2024-2025',
            'annee' => '2024-2025'
        ]);

        // Pour chaque niveau, créer une classe
        $niveaux = Niveau::all();
        foreach ($niveaux as $niveau) {
            Classe::create([
                'nom' => $niveau->nom,
                'niveau_id' => $niveau->id,
                'session_id' => $session->id
            ]);
        }
    }
}
