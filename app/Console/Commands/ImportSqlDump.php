<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSqlDump extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:import-sql {path=database/dumps/ecoleprimaire.sql} {--fresh : Run migrate:fresh before import}';

    /**
     * The console command description.
     */
    protected $description = 'Importe un fichier .sql dans la base configurée (option --fresh pour recréer le schéma).';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (!File::exists($path)) {
            $this->error("Fichier introuvable: {$path}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Exécution de migrate:fresh (toutes les tables seront recréées) ...');
            Artisan::call('migrate:fresh', [
                '--force' => true,
            ]);
            $this->info(trim(Artisan::output()));
        }

        $this->info('Lecture du dump SQL ...');
        $sql = File::get($path);

        // Retirer les commentaires et normaliser
        $normalized = $this->stripSqlComments($sql);

        // Découper en statements (naïf mais OK sans DELIMITER)
        $statements = $this->splitSqlStatements($normalized);

        if (empty($statements)) {
            $this->error('Aucune instruction SQL détectée.');
            return self::FAILURE;
        }

        $this->info('Import en cours ...');
        // Désactiver les contraintes de clés étrangères pour éviter les erreurs d'ordre d'insertion
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } catch (\Throwable $e) {
            // Ignorer si le SGBD ne supporte pas (ex: SQLite)
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($statements as $stmt) {
                $trim = trim($stmt);
                if ($trim === '') continue;

                // éviter d'exécuter du SQL de contrôle de transaction présent dans le dump
                $upper = strtoupper($trim);
                if (str_starts_with($upper, 'START TRANSACTION') || str_starts_with($upper, 'COMMIT') || str_starts_with($upper, 'ROLLBACK')) {
                    continue;
                }

                DB::unprepared($trim);
                $count++;
                if ($count % 50 === 0) {
                    $this->line(" - {$count} statements exécutés ...");
                }
            }
            DB::commit();
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $e) {}
            $this->info("Import terminé: {$count} statements exécutés.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur pendant l\'import: ' . $e->getMessage());
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $e2) {}
            return self::FAILURE;
        }
    }

    private function stripSqlComments(string $sql): string
    {
        // Supprimer blocs /* ... */
        $sql = preg_replace('#/\*.*?\*/#s', '', $sql) ?? $sql;
        // Supprimer lignes commençant par -- ou #
        $lines = preg_split("/(\r\n|\r|\n)/", $sql) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            $trim = ltrim($line);
            if (str_starts_with($trim, '--') || str_starts_with($trim, '#')) {
                continue;
            }
            $kept[] = $line;
        }
        return implode("\n", $kept);
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inSingle = false;
        $inDouble = false;

        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === "'" && !$inDouble) {
                $inSingle = !$inSingle;
            } elseif ($ch === '"' && !$inSingle) {
                $inDouble = !$inDouble;
            }

            if ($ch === ';' && !$inSingle && !$inDouble) {
                $statements[] = $current;
                $current = '';
                continue;
            }
            $current .= $ch;
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }
}
