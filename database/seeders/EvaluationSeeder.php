<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;
use App\Models\Session;
use App\Models\Enseignant;

class EvaluationSeeder extends Seeder
{
    public function run()
    {
        $sessions = Session::all();
        $classes = Classe::all();
        $now = now();
        foreach ($sessions as $session) {
            foreach ($classes as $classe) {
                $enseignant = Enseignant::where('classe_id', $classe->id)->first() ?? Enseignant::first();
                if (!$enseignant) continue;
                for ($i = 1; $i <= 9; $i++) {
                    DB::table('evaluations')->updateOrInsert([
                        'session_id' => $session->id,
                        'classe_id' => $classe->id,
                        'numero_eval' => $i,
                    ], [
                        'trimestre' => ceil($i/3),
                        'date_eval' => $now->copy()->addMonths($i-1),
                        'enseignant_id' => $enseignant->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
} 