<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Note;
use App\Models\Evaluation;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize {--clean-notes : Nettoyer les notes invalides} {--fix-evaluations : Corriger les évaluations manquantes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise la base de données et nettoie les données invalides';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Début de l\'optimisation de la base de données...');

        // Nettoyer les caches
        $this->info('Nettoyage des caches...');
        Cache::flush();
        $this->info('✓ Caches nettoyés');

        // Optimiser les tables
        $this->info('Optimisation des tables...');
        $tables = ['notes', 'evaluations', 'eleves', 'classes', 'sessions', 'competences', 'sous_competences', 'modalites'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->info("✓ Table {$table} optimisée");
            }
        }

        // Nettoyer les notes invalides si demandé
        if ($this->option('clean-notes')) {
            $this->cleanInvalidNotes();
        }

        // Corriger les évaluations manquantes si demandé
        if ($this->option('fix-evaluations')) {
            $this->fixMissingEvaluations();
        }

        // Analyser les tables pour optimiser les requêtes
        $this->info('Analyse des tables...');
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ANALYZE TABLE {$table}");
                $this->info("✓ Table {$table} analysée");
            }
        }

        $this->info('Optimisation terminée avec succès !');
        return 0;
    }

    /**
     * Nettoie les notes invalides
     */
    private function cleanInvalidNotes()
    {
        $this->info('Nettoyage des notes invalides...');

        // Supprimer les notes avec des valeurs négatives
        $deleted = Note::where('valeur', '<', 0)->delete();
        $this->info("✓ {$deleted} notes négatives supprimées");

        // Supprimer les notes avec des valeurs trop élevées (plus de 100)
        $deleted = Note::where('valeur', '>', 100)->delete();
        $this->info("✓ {$deleted} notes trop élevées supprimées");

        // Supprimer les notes orphelines (sans élève, sous-compétence ou modalité)
        $deleted = Note::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('eleves')
                  ->whereRaw('eleves.id = notes.eleve_id');
        })->delete();
        $this->info("✓ {$deleted} notes orphelines (élèves) supprimées");

        $deleted = Note::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('sous_competences')
                  ->whereRaw('sous_competences.id = notes.sous_competence_id');
        })->delete();
        $this->info("✓ {$deleted} notes orphelines (sous-compétences) supprimées");

        $deleted = Note::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('modalites')
                  ->whereRaw('modalites.id = notes.modalite_id');
        })->delete();
        $this->info("✓ {$deleted} notes orphelines (modalités) supprimées");
    }

    /**
     * Corrige les évaluations manquantes
     */
    private function fixMissingEvaluations()
    {
        $this->info('Correction des évaluations manquantes...');

        // Récupérer toutes les sessions et classes
        $sessions = DB::table('sessions')->get();
        $classes = DB::table('classes')->get();

        $created = 0;
        foreach ($sessions as $session) {
            foreach ($classes as $classe) {
                // Créer les évaluations manquantes (UA 1 à 9)
                for ($i = 1; $i <= 9; $i++) {
                    $exists = Evaluation::where('session_id', $session->id)
                        ->where('classe_id', $classe->id)
                        ->where('numero_eval', $i)
                        ->exists();

                    if (!$exists) {
                        Evaluation::create([
                            'enseignant_id' => 1, // ID par défaut
                            'session_id' => $session->id,
                            'classe_id' => $classe->id,
                            'numero_eval' => $i,
                            'trimestre' => $this->getTrimestreFromUA($i),
                            'date_eval' => now()
                        ]);
                        $created++;
                    }
                }
            }
        }

        $this->info("✓ {$created} évaluations créées");
    }

    /**
     * Détermine le trimestre à partir du numéro d'UA
     */
    private function getTrimestreFromUA($ua)
    {
        if ($ua <= 3) return 'T1';
        if ($ua <= 6) return 'T2';
        return 'T3';
    }
} 