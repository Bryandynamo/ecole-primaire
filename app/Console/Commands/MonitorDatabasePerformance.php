<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonitorDatabasePerformance extends Command
{
    protected $signature = 'db:monitor {--etablissement=} {--threshold=1000}';
    protected $description = 'Monitorer les performances de la base de données';

    public function handle()
    {
        $etablissementId = $this->option('etablissement');
        $threshold = (int) $this->option('threshold');

        $this->info('🔍 Monitoring des performances de la base de données...');

        // 1. Vérifier la taille des tables
        $this->checkTableSizes($etablissementId);

        // 2. Vérifier les requêtes lentes
        $this->checkSlowQueries();

        // 3. Vérifier l'utilisation du cache
        $this->checkCacheUsage();

        // 4. Vérifier les connexions
        $this->checkConnections();

        // 5. Alertes si nécessaire
        $this->sendAlerts($threshold);

        $this->info('✅ Monitoring terminé');
    }

    private function checkTableSizes($etablissementId = null)
    {
        $this->info('📊 Vérification de la taille des tables...');

        $query = "
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
                table_rows
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            AND table_name IN ('notes', 'eleves', 'classes', 'evaluations')
            ORDER BY (data_length + index_length) DESC
        ";

        $results = DB::select($query);

        foreach ($results as $table) {
            $this->line("  - {$table->table_name}: {$table->{'Size (MB)'}} MB ({$table->table_rows} lignes)");
            
            if ($table->{'Size (MB)'} > 1000) {
                Log::warning("Table {$table->table_name} dépasse 1GB", [
                    'size_mb' => $table->{'Size (MB)'},
                    'rows' => $table->table_rows
                ]);
            }
        }
    }

    private function checkSlowQueries()
    {
        $this->info('⏱️ Vérification des requêtes lentes...');

        // Simuler une requête de statistiques
        $startTime = microtime(true);
        
        $count = DB::table('notes')
            ->where('etablissement_id', 1)
            ->count();
            
        $duration = (microtime(true) - $startTime) * 1000;

        $this->line("  - Requête de comptage: {$duration}ms pour {$count} notes");

        if ($duration > 1000) {
            Log::warning('Requête lente détectée', [
                'duration_ms' => $duration,
                'count' => $count
            ]);
        }
    }

    private function checkCacheUsage()
    {
        $this->info('💾 Vérification de l\'utilisation du cache...');

        $cacheStats = Cache::getRedis()->info('memory');
        $memoryUsage = $cacheStats['used_memory_human'] ?? 'N/A';
        $hitRate = $cacheStats['keyspace_hits'] ?? 0;
        $missRate = $cacheStats['keyspace_misses'] ?? 0;

        $this->line("  - Utilisation mémoire: {$memoryUsage}");
        $this->line("  - Hit rate: " . ($hitRate > 0 ? round(($hitRate / ($hitRate + $missRate)) * 100, 2) : 0) . "%");
    }

    private function checkConnections()
    {
        $this->info('🔌 Vérification des connexions...');

        $connections = DB::select("SHOW STATUS LIKE 'Threads_connected'");
        $maxConnections = DB::select("SHOW VARIABLES LIKE 'max_connections'");

        $current = $connections[0]->Value ?? 0;
        $max = $maxConnections[0]->Value ?? 0;
        $usage = $max > 0 ? round(($current / $max) * 100, 2) : 0;

        $this->line("  - Connexions actives: {$current}/{$max} ({$usage}%)");

        if ($usage > 80) {
            Log::warning('Utilisation élevée des connexions', [
                'current' => $current,
                'max' => $max,
                'usage_percent' => $usage
            ]);
        }
    }

    private function sendAlerts($threshold)
    {
        $this->info('🚨 Vérification des alertes...');

        // Vérifier le nombre de notes par établissement
        $notesCount = DB::table('notes')
            ->select('etablissement_id', DB::raw('COUNT(*) as count'))
            ->groupBy('etablissement_id')
            ->having('count', '>', $threshold)
            ->get();

        foreach ($notesCount as $etab) {
            Log::alert("Établissement {$etab->etablissement_id} dépasse le seuil de {$threshold} notes", [
                'etablissement_id' => $etab->etablissement_id,
                'notes_count' => $etab->count,
                'threshold' => $threshold
            ]);

            $this->warn("⚠️ Établissement {$etab->etablissement_id}: {$etab->count} notes (> {$threshold})");
        }
    }
} 