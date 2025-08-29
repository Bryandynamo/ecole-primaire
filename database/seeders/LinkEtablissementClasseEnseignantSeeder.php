<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Etablissement;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\User;

class LinkEtablissementClasseEnseignantSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1) Ensure an establishment exists
            $etab = Etablissement::firstOrCreate(
                ['code' => 'ECOLE-DEMO'],
                ['nom' => 'Ecole Démo']
            );

            // 2) Attach classes to establishment (default to first class if any)
            $classe = Classe::first();
            if ($classe && !$classe->etablissement_id) {
                $classe->etablissement_id = $etab->id;
                $classe->save();
            }

            // 3) Create or link a user account for the first teacher and attach etablissement
            $ens = Enseignant::first();
            if ($ens) {
                // create user if missing
                if (empty($ens->user_id)) {
                    $email = 'enseignant.demo@example.com';
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => trim(($ens->prenom ?? '').' '.($ens->nom ?? 'Enseignant')),
                            'password' => Hash::make('password'),
                        ]
                    );
                    $ens->user_id = $user->id;
                }
                if (empty($ens->etablissement_id)) {
                    $ens->etablissement_id = $classe?->etablissement_id ?: $etab->id;
                }
                $ens->save();
            }
        });
    }
}
