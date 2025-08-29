<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            EtablissementSeeder::class,
            RealDataSeeder::class,
            LeconSeeder::class,
            LeconsDemoSeeder::class,
            LeconEvaluationSeeder::class,
            LinkEtablissementClasseEnseignantSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Ajout de classes pour chaque niveau et chaque session
        // $niveaux = [
        //     1 => 'CP',
        //     2 => 'CE1',
        //     3 => 'CE2',
        //     4 => 'CM1',
        //     5 => 'CM2',
        // ];
        // $sessions = \App\Models\Session::orderByDesc('id')->take(2)->get(); // session actuelle + nouvelle
        // if ($sessions->count() < 2) {
        //     // Crée une nouvelle session si besoin
        //     $lastSession = \App\Models\Session::orderByDesc('id')->first();
        //     $newSession = \App\Models\Session::create([
        //         'nom' => 'Nouvelle Session',
        //         'date_debut' => now(),
        //         'date_fin' => now()->addMonths(9),
        //     ]);
        //     $sessions = collect([$lastSession, $newSession]);
        // }
        // foreach ($niveaux as $niveau_id => $nom) {
        //     foreach ($sessions as $session) {
        //         \App\Models\Classe::updateOrCreate([
        //             'niveau_id' => $niveau_id,
        //             'session_id' => $session->id,
        //         ], [
        //             'nom' => $nom,
        //         ]);
        //     }
        // }
    }
}
