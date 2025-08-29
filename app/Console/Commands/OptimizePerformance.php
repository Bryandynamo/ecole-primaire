<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize {--clear-cache : Nettoyer tous les caches} {--clear-views : Nettoyer les vues compilées} {--optimize-db : Optimiser la base de données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise les performances de l\'application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Début de l\'optimisation des performances...');

        // Nettoyer les caches
        if ($this->option('clear-cache') || $this->option('clear-views')) {
            $this->info('🧹 Nettoyage des caches...');
            
            if ($this->option('clear-cache')) {
                Cache::flush();
                $this->info('✓ Cache de données nettoyé');
            }
            
            if ($this->option('clear-views')) {
                Artisan::call('view:clear');
                $this->info('✓ Cache des vues nettoyé');
            }
            
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            $this->info('✓ Caches système nettoyés');
        }

        // Optimiser la base de données
        if ($this->option('optimize-db')) {
            $this->info('🗄️ Optimisation de la base de données...');
            Artisan::call('db:optimize', ['--clean-notes' => true, '--fix-evaluations' => true]);
        }

        // Optimiser les performances PHP
        $this->info('⚡ Optimisation des performances PHP...');
        
        // Vérifier et optimiser les paramètres PHP
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        
        $this->info("Mémoire actuelle: {$memoryLimit}");
        $this->info("Temps d'exécution max: {$maxExecutionTime}s");
        
        if ($memoryLimit !== '512M') {
            $this->warn('⚠️ Considérez augmenter memory_limit à 512M dans php.ini');
        }
        
        if ($maxExecutionTime < 300) {
            $this->warn('⚠️ Considérez augmenter max_execution_time à 300 dans php.ini');
        }

        // Optimiser les tables si possible
        $this->info('📊 Optimisation des tables...');
        $tables = ['notes', 'evaluations', 'eleves', 'classes', 'sessions', 'competences', 'sous_competences', 'modalites'];
        
        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->info("✓ Table {$table} optimisée");
            } catch (\Exception $e) {
                $this->warn("⚠️ Impossible d'optimiser la table {$table}: " . $e->getMessage());
            }
        }

        // Vérifier les index
        $this->info('🔍 Vérification des index...');
        $this->checkIndexes();

        // Recommandations
        $this->info('💡 Recommandations pour améliorer les performances:');
        $this->line('1. Activez OPcache dans PHP');
        $this->line('2. Utilisez Redis pour le cache si possible');
        $this->line('3. Optimisez les requêtes MySQL avec EXPLAIN');
        $this->line('4. Surveillez les logs de performance');

        $this->info('✅ Optimisation terminée avec succès !');
        return 0;
    }

    /**
     * Vérifie les index importants
     */
    private function checkIndexes()
    {
        $importantIndexes = [
            'notes' => ['session_id', 'classe_id', 'evaluation_id', 'eleve_id'],
            'evaluations' => ['session_id', 'classe_id', 'numero_eval'],
            'eleves' => ['classe_id', 'session_id'],
        ];

        foreach ($importantIndexes as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    $indexExists = DB::select("SHOW INDEX FROM {$table} WHERE Column_name = '{$column}'");
                    if (empty($indexExists)) {
                        $this->warn("⚠️ Index manquant: {$table}.{$column}");
                    } else {
                        $this->info("✓ Index présent: {$table}.{$column}");
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️ Impossible de vérifier l'index {$table}.{$column}");
                }
            }
        }
    }
} 