<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Etablissement;

class EtablissementSeeder extends Seeder
{
    public function run(): void
    {
        Etablissement::updateOrCreate(
            ['code' => 'ECOLE-DEMO'],
            [
                'nom' => 'Ecole Démo',
                'adresse' => 'Centre-Ville',
                'telephone' => '000000000',
            ]
        );
    }
}
