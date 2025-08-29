<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Note;
use App\Models\Classe;
use App\Models\Session;

class StatistiquesCacheService
{
    private const CACHE_TTL = 3600; // 1 heure
    private const CACHE_PREFIX = 'stats_';

    /**
     * Générer et mettre en cache les statistiques
     */
    public function getStatistiquesCachees(int $etablissementId, int $sessionId, int $classeId, $periode = 1): array
    {
        $cacheKey = $this->generateCacheKey($etablissementId, $sessionId, $classeId, $periode);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($etablissementId, $sessionId, $classeId, $periode) {
            return $this->calculerStatistiques($etablissementId, $sessionId, $classeId, $periode);
        });
    }

    /**
     * Invalider le cache quand les notes changent
     */
    public function invaliderCache(int $etablissementId, int $sessionId, int $classeId): void
    {
        $patterns = [
            $this->generateCacheKey($etablissementId, $sessionId, $classeId, '*'),
            $this->generateCacheKey($etablissementId, $sessionId, $classeId, 'trimestre*'),
        ];

        foreach ($patterns as $pattern) {
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * Calculer les statistiques avec optimisation
     */
    private function calculerStatistiques(int $etablissementId, int $sessionId, int $classeId, $periode): array
    {
        try {
            // Utiliser des requêtes optimisées avec des index
            $query = Note::where('etablissement_id', $etablissementId)
                        ->where('session_id', $sessionId)
                        ->where('classe_id', $classeId);

            // Filtrer par période
            if (is_numeric($periode)) {
                $evalIds = $this->getEvalIdsForTrimestre($classeId, $sessionId, $periode);
                $query->whereIn('evaluation_id', $evalIds);
            } elseif (preg_match('/trimestre(\d)/i', $periode, $m)) {
                $trimestre = (int)$m[1];
                $query->where('trimestre', $trimestre);
            }

            $notes = $query->with(['eleve', 'evaluation'])
                          ->get()
                          ->groupBy('eleve_id');

            // Calculs optimisés
            $stats = $this->calculerStatsOptimisees($notes);

            Log::info('Statistiques calculées et mises en cache', [
                'etablissement_id' => $etablissementId,
                'session_id' => $sessionId,
                'classe_id' => $classeId,
                'periode' => $periode,
                'nb_eleves' => count($notes)
            ]);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques', [
                'etablissement_id' => $etablissementId,
                'session_id' => $sessionId,
                'classe_id' => $classeId,
                'periode' => $periode,
                'error' => $e->getMessage()
            ]);
            
            return $this->getStatsParDefaut();
        }
    }

    /**
     * Calculer les statistiques de manière optimisée
     */
    private function calculerStatsOptimisees($notesGrouped): array
    {
        $moyennes = [];
        $moyennes_garcons = [];
        $moyennes_filles = [];
        $ayant_compose = 0;
        $ayant_compose_garcons = 0;
        $ayant_compose_filles = 0;
        $admis = 0;
        $admis_garcons = 0;
        $admis_filles = 0;
        $echoues = 0;
        $echoues_garcons = 0;
        $echoues_filles = 0;

        foreach ($notesGrouped as $eleveId => $notesEleve) {
            $eleve = $notesEleve->first()->eleve;
            $total = 0;
            $totalMax = 0;
            $hasNote = false;

            foreach ($notesEleve as $note) {
                if ($note->valeur !== null && is_numeric($note->valeur)) {
                    $hasNote = true;
                    $total += (float)$note->valeur;
                    // Calculer les points max depuis la relation modalite
                    $pointsMax = $note->modalite->pivot->points_max ?? 0;
                    $totalMax += (float)$pointsMax;
                }
            }

            // Correction : si totalMax > 0, calcul normal. Sinon, si au moins une note existe, on considère la moyenne brute (somme / nombre de notes)
            if ($hasNote) {
                if ($totalMax > 0) {
                    $moyenne = round(($total / $totalMax) * 20, 2);
                } else {
                    $nbNotes = $notesEleve->filter(fn($n) => $n->valeur !== null && is_numeric($n->valeur))->count();
                    $moyenne = $nbNotes > 0 ? round($total / $nbNotes, 2) : 0;
                }
                $moyennes[] = $moyenne;

                if ($eleve->sexe === 'M') {
                    $moyennes_garcons[] = $moyenne;
                    $ayant_compose_garcons++;
                    if ($moyenne >= 10) $admis_garcons++;
                    else $echoues_garcons++;
                } elseif ($eleve->sexe === 'F') {
                    $moyennes_filles[] = $moyenne;
                    $ayant_compose_filles++;
                    if ($moyenne >= 10) $admis_filles++;
                    else $echoues_filles++;
                }

                $ayant_compose++;
                if ($moyenne >= 10) $admis++;
                else $echoues++;
            }
        }

        // Correction : compter les inscrits sur la vraie liste des élèves de la classe
        $classe = \App\Models\Classe::with('eleves')->find($classeId);
        $eleves = $classe ? $classe->eleves : collect();
        $inscrits = $eleves->count();
        $inscrits_garcons = $eleves->where('sexe', 'M')->count();
        $inscrits_filles = $eleves->where('sexe', 'F')->count();

        return [
            'inscrits' => $inscrits,
            'inscrits_garcons' => $inscrits_garcons,
            'inscrits_filles' => $inscrits_filles,
            'ayant_compose' => $ayant_compose,
            'ayant_compose_garcons' => $ayant_compose_garcons,
            'ayant_compose_filles' => $ayant_compose_filles,
            'admis' => $admis,
            'admis_garcons' => $admis_garcons,
            'admis_filles' => $admis_filles,
            'echoues' => $echoues,
            'echoues_garcons' => $echoues_garcons,
            'echoues_filles' => $echoues_filles,
            'pourc_reussite' => $ayant_compose > 0 ? round($admis * 100 / $ayant_compose, 2) : 0,
            'pourc_reussite_garcons' => $ayant_compose_garcons > 0 ? round($admis_garcons * 100 / $ayant_compose_garcons, 2) : 0,
            'pourc_reussite_filles' => $ayant_compose_filles > 0 ? round($admis_filles * 100 / $ayant_compose_filles, 2) : 0,
            'pourc_echec' => $ayant_compose > 0 ? round($echoues * 100 / $ayant_compose, 2) : 0,
            'pourc_echec_garcons' => $ayant_compose_garcons > 0 ? round($echoues_garcons * 100 / $ayant_compose_garcons, 2) : 0,
            'pourc_echec_filles' => $ayant_compose_filles > 0 ? round($echoues_filles * 100 / $ayant_compose_filles, 2) : 0,
            'moyenne_generale' => count($moyennes) > 0 ? round(array_sum($moyennes) / count($moyennes), 2) : 0,
            'moyenne_generale_garcons' => count($moyennes_garcons) > 0 ? round(array_sum($moyennes_garcons) / count($moyennes_garcons), 2) : 0,
            'moyenne_generale_filles' => count($moyennes_filles) > 0 ? round(array_sum($moyennes_filles) / count($moyennes_filles), 2) : 0,
            'moyenne_premier' => count($moyennes) > 0 ? round(max($moyennes), 2) : '-',
            'moyenne_dernier' => count($moyennes) > 0 ? round(min($moyennes), 2) : '-',
            'moyenne_premier_garcons' => count($moyennes_garcons) > 0 ? round(max($moyennes_garcons), 2) : '-',
            'moyenne_dernier_garcons' => count($moyennes_garcons) > 0 ? round(min($moyennes_garcons), 2) : '-',
            'moyenne_premier_filles' => count($moyennes_filles) > 0 ? round(max($moyennes_filles), 2) : '-',
            'moyenne_dernier_filles' => count($moyennes_filles) > 0 ? round(min($moyennes_filles), 2) : '-',
        ];
    }

    /**
     * Générer la clé de cache
     */
    private function generateCacheKey(int $etablissementId, int $sessionId, int $classeId, $periode): string
    {
        return self::CACHE_PREFIX . "{$etablissementId}_{$sessionId}_{$classeId}_{$periode}";
    }

    /**
     * Nettoyer le cache par pattern
     */
    private function clearCacheByPattern(string $pattern): void
    {
        // Implémentation dépendante du driver de cache
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Obtenir les IDs d'évaluation pour un trimestre
     */
    private function getEvalIdsForTrimestre(int $classeId, int $sessionId, $periode): array
    {
        // Logique existante pour obtenir les IDs d'évaluation
        // À adapter selon votre logique métier
        return \App\Models\Evaluation::where('classe_id', $classeId)
            ->where('session_id', $sessionId)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Statistiques par défaut en cas d'erreur
     */
    private function getStatsParDefaut(): array
    {
        return [
            'inscrits' => 0,
            'inscrits_garcons' => 0,
            'inscrits_filles' => 0,
            'ayant_compose' => 0,
            'ayant_compose_garcons' => 0,
            'ayant_compose_filles' => 0,
            'admis' => 0,
            'admis_garcons' => 0,
            'admis_filles' => 0,
            'echoues' => 0,
            'echoues_garcons' => 0,
            'echoues_filles' => 0,
            'pourc_reussite' => 0,
            'pourc_reussite_garcons' => 0,
            'pourc_reussite_filles' => 0,
            'pourc_echec' => 0,
            'pourc_echec_garcons' => 0,
            'pourc_echec_filles' => 0,
            'moyenne_generale' => 0,
            'moyenne_generale_garcons' => 0,
            'moyenne_generale_filles' => 0,
            'moyenne_premier' => '-',
            'moyenne_dernier' => '-',
            'moyenne_premier_garcons' => '-',
            'moyenne_dernier_garcons' => '-',
            'moyenne_premier_filles' => '-',
            'moyenne_dernier_filles' => '-',
        ];
    }
} 