<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SyntheseCompetencesExport implements FromArray
{
    protected $classe;
    protected $session;
    protected $evaluation;
    protected $synthese;
    protected $totaux;

    public function __construct($classe, $session, $evaluation, $synthese, $totaux = null)
    {
        $this->classe = $classe;
        $this->session = $session;
        $this->evaluation = $evaluation;
        $this->synthese = $synthese;
        $this->totaux = $totaux;
    }

    public function array(): array
    {
        $rows = [];
        // Titre
        $rows[] = [
            'Synthèse des compétences par sous-compétence - Classe: ' . ($this->classe->nom ?? '') . ' | Évaluation: ' . ($this->evaluation->libelle ?? $this->evaluation->id) . ' | Année: ' . ($this->session->nom ?? '')
        ];
        // En-têtes sur 2 lignes comme dans la vue
        $rows[] = [
            'Contenus sous-compétence',
            'Inscrits G','Inscrits F','Inscrits T',
            'Présents G','Présents F','Présents T',
            'Experts G','Experts F','Experts T','Experts G %','Experts F %','Experts T %',
            'Acquis G','Acquis F','Acquis T','Acquis G %','Acquis F %','Acquis T %',
            'Encours G','Encours F','Encours T','Encours G %','Encours F %','Encours T %',
            'Non acquis G','Non acquis F','Non acquis T','Non acquis G %','Non acquis F %','Non acquis T %'
        ];

        // Défaut: toutes colonnes numériques à 0
        $defaults = [
            'inscrits_g'=>0,'inscrits_f'=>0,'inscrits_t'=>0,
            'present_g'=>0,'present_f'=>0,'present_t'=>0,
            'experts_g'=>0,'experts_f'=>0,'experts_t'=>0,
            'experts_g_p'=>0,'experts_f_p'=>0,'experts_t_p'=>0,
            'acquis_g'=>0,'acquis_f'=>0,'acquis_t'=>0,
            'acquis_g_p'=>0,'acquis_f_p'=>0,'acquis_t_p'=>0,
            'encours_g'=>0,'encours_f'=>0,'encours_t'=>0,
            'encours_g_p'=>0,'encours_f_p'=>0,'encours_t_p'=>0,
            'nonacquis_g'=>0,'nonacquis_f'=>0,'nonacquis_t'=>0,
            'nonacquis_g_p'=>0,'nonacquis_f_p'=>0,'nonacquis_t_p'=>0,
        ];

        foreach ($this->synthese as $row) {
            if (!is_array($row)) { $row = []; }
            $row = array_merge($defaults, $row);
            $rows[] = [
                $row['sous_competence'] ?? '',
                $this->zi($row['inscrits_g'] ?? null),
                $this->zi($row['inscrits_f'] ?? null),
                $this->zi($row['inscrits_t'] ?? null),
                $this->zi($row['present_g'] ?? null),
                $this->zi($row['present_f'] ?? null),
                $this->zi($row['present_t'] ?? null),
                $this->zi($row['experts_g'] ?? null),
                $this->zi($row['experts_f'] ?? null),
                $this->zi($row['experts_t'] ?? null),
                $this->zf($row['experts_g_p'] ?? null),
                $this->zf($row['experts_f_p'] ?? null),
                $this->zf($row['experts_t_p'] ?? null),
                $this->zi($row['acquis_g'] ?? null),
                $this->zi($row['acquis_f'] ?? null),
                $this->zi($row['acquis_t'] ?? null),
                $this->zf($row['acquis_g_p'] ?? null),
                $this->zf($row['acquis_f_p'] ?? null),
                $this->zf($row['acquis_t_p'] ?? null),
                $this->zi($row['encours_g'] ?? null),
                $this->zi($row['encours_f'] ?? null),
                $this->zi($row['encours_t'] ?? null),
                $this->zf($row['encours_g_p'] ?? null),
                $this->zf($row['encours_f_p'] ?? null),
                $this->zf($row['encours_t_p'] ?? null),
                $this->zi($row['nonacquis_g'] ?? null),
                $this->zi($row['nonacquis_f'] ?? null),
                $this->zi($row['nonacquis_t'] ?? null),
                $this->zf($row['nonacquis_g_p'] ?? null),
                $this->zf($row['nonacquis_f_p'] ?? null),
                $this->zf($row['nonacquis_t_p'] ?? null),
            ];
        }

        // Ajouter des lignes 0 pour les sous-compétences manquantes (aucune ligne dans $this->synthese)
        try {
            $presentLabels = collect($this->synthese)->pluck('sous_competence')->filter()->values()->all();
            if (isset($this->classe->niveau) && isset($this->classe->niveau->competences)) {
                foreach ($this->classe->niveau->competences as $comp) {
                    if (!isset($comp->sousCompetences)) continue;
                    foreach ($comp->sousCompetences as $sc) {
                        $label = $sc->nom ?? $sc->libelle ?? null;
                        if (!$label) continue;
                        if (!in_array($label, $presentLabels, true)) {
                            $rows[] = [
                                $label,
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zf(0), $this->zf(0), $this->zf(0),
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zf(0), $this->zf(0), $this->zf(0),
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zf(0), $this->zf(0), $this->zf(0),
                                $this->zi(0), $this->zi(0), $this->zi(0),
                                $this->zf(0), $this->zf(0), $this->zf(0),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // En cas d'objet non chargé, on ignore silencieusement
        }

        if ($this->totaux) {
            $t = is_array($this->totaux) ? array_merge($defaults, $this->totaux) : $defaults;
            $rows[] = [
                'TOTAL',
                $this->zi($t['inscrits_g'] ?? null),
                $this->zi($t['inscrits_f'] ?? null),
                $this->zi($t['inscrits_t'] ?? null),
                $this->zi($t['present_g'] ?? null),
                $this->zi($t['present_f'] ?? null),
                $this->zi($t['present_t'] ?? null),
                $this->zi($t['experts_g'] ?? null),
                $this->zi($t['experts_f'] ?? null),
                $this->zi($t['experts_t'] ?? null),
                $this->zf($t['experts_g_p'] ?? null),
                $this->zf($t['experts_f_p'] ?? null),
                $this->zf($t['experts_t_p'] ?? null),
                $this->zi($t['acquis_g'] ?? null),
                $this->zi($t['acquis_f'] ?? null),
                $this->zi($t['acquis_t'] ?? null),
                $this->zf($t['acquis_g_p'] ?? null),
                $this->zf($t['acquis_f_p'] ?? null),
                $this->zf($t['acquis_t_p'] ?? null),
                $this->zi($t['encours_g'] ?? null),
                $this->zi($t['encours_f'] ?? null),
                $this->zi($t['encours_t'] ?? null),
                $this->zf($t['encours_g_p'] ?? null),
                $this->zf($t['encours_f_p'] ?? null),
                $this->zf($t['encours_t_p'] ?? null),
                $this->zi($t['nonacquis_g'] ?? null),
                $this->zi($t['nonacquis_f'] ?? null),
                $this->zi($t['nonacquis_t'] ?? null),
                $this->zf($t['nonacquis_g_p'] ?? null),
                $this->zf($t['nonacquis_f_p'] ?? null),
                $this->zf($t['nonacquis_t_p'] ?? null),
            ];
        }

        return $rows;
    }

    // Convertit vide -> 0 et normalise les numériques (gère espaces, virgules, %)
    private function z($v)
    {
        if ($v === null) return 0;
        if (is_string($v)) {
            $s = trim($v);
            if ($s === '') return 0;
            // supprimer % et espaces, convertir virgule en point
            $s = str_replace(['%',' '], '', $s);
            $s = str_replace(',', '.', $s);
            if (is_numeric($s)) return 0 + $s;
            return 0;
        }
        if (is_numeric($v)) return 0 + $v;
        return 0;
    }

    // Entier strict: retourne '0' (string) si zéro pour forcer l'affichage dans Excel même si les zéros sont masqués
    private function zi($v)
    {
        $n = (int)$this->z($v);
        return $n === 0 ? '0' : $n;
    }
    // Décimal strict: retourne '0' (string) si zéro pour forcer l'affichage dans Excel
    private function zf($v)
    {
        $f = (float)$this->z($v);
        // On peut arrondir à 1 décimale si besoin, mais '0' reste préférable pour homogénéité
        return $f == 0.0 ? '0' : $f;
    }
}
