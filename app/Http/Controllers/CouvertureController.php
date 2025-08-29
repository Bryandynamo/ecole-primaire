<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Couverture;
use App\Models\Evaluation;
use App\Models\Lecon;
use App\Models\SousCompetence;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouvertureController extends Controller
{
    // ... (show, save, exportPdf methods as defined in the thought block) ...
    /**
     * Affiche la fiche de couverture pour une classe et une évaluation (UA).
     */
    public function show($classe_id, $evaluation_id)
    {
        Log::info('Début - CouvertureController@show', ['classe_id' => $classe_id, 'evaluation_id' => $evaluation_id]);

        $classe = Classe::with('niveau')->findOrFail($classe_id);
        $evaluation = Evaluation::findOrFail($evaluation_id);

        $data = $this->prepareCouvertureData($classe, $evaluation);

    Log::info('Fin - CouvertureController@show - Données envoyées à la vue.');
    return view('couverture.show', $data);
    }

    /**
     * Enregistrement AJAX d'une saisie de couverture.
     */
    public function updateCouverture(Request $request)
    {
        $classe_id = $request->input('classe_id');
        $data = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'evaluation_id' => 'required|exists:evaluations,id',
            'lecon_id' => 'required|exists:lecons,id',
            'nb_couverts' => 'required|integer|min:0',
        ]);

        $lecon = Lecon::findOrFail($data['lecon_id']);
        $evaluation = Evaluation::findOrFail($data['evaluation_id']);
        $classe = Classe::findOrFail($data['classe_id']);

        // Validation stricte : empêcher le dépassement des heures prévues
        $colUa = 'total_a_couvrir_ua' . $evaluation->numero_eval;
        $heuresPrevues = $lecon->{$colUa} ?? 0;
        if ($data['nb_couverts'] > $heuresPrevues) {
            return response()->json([
                'success' => false,
                'message' => "Impossible d'enregistrer plus de $heuresPrevues heures pour cette leçon."
            ], 422);
        }

        // Mise à jour de la couverture
        Couverture::updateOrCreate(
            ['lecon_id' => $data['lecon_id'], 'evaluation_id' => $data['evaluation_id'], 'classe_id' => $data['classe_id']],
            ['sous_competence_id' => $lecon->sous_competence_id, 'nb_couverts' => $data['nb_couverts']]
        );

        // Recalculer toutes les données pour la réponse JSON
        $allEvaluations = $this->getEvaluationsForClasse($classe->id);
        $colUa = 'total_a_couvrir_ua' . $evaluation->numero_eval;
        $heuresPrevues = $lecon->{$colUa} ?? 0;

        // 1. Stats pour la leçon mise à jour
        $leconStats = $this->calculateStatsForLecon($lecon, $evaluation, $allEvaluations, $heuresPrevues);

        // 2. Recalculer les totaux pour la sous-compétence concernée
        $sc_id = $lecon->sous_competence_id;
        $leconsPourSC = Lecon::where('sous_competence_id', $sc_id)->get();
        $scTotals = [
            'total_prevu_ua' => 0, 'nb_courant' => 0, 'taux_ua' => 0,
            'total_trimestre' => 0, 'nb_couverts_trimestre' => 0, 'taux_trimestre' => 0,
            'total_annee' => 0, 'nb_couverts_annee' => 0, 'taux_annee' => 0
        ];

        foreach ($leconsPourSC as $leconInSc) {
            $heuresPrevuesUaCol = 'total_a_couvrir_ua' . $evaluation->numero_eval;
            $heuresPrevues = $leconInSc->{$heuresPrevuesUaCol} ?? 0;
            $stats = $this->calculateStatsForLecon($leconInSc, $evaluation, $allEvaluations, $heuresPrevues);
            $scTotals['total_prevu_ua'] += $stats['total_a_couvrir_ua'];
            $scTotals['nb_courant'] += $stats['nb_courant'];
            $scTotals['total_trimestre'] += $stats['total_trimestre'];
            $scTotals['nb_couverts_trimestre'] += $stats['nb_couverts_trimestre'];
            $scTotals['total_annee'] += $stats['total_annee'];
            $scTotals['nb_couverts_annee'] += $stats['nb_couverts_annee'];
        }

        // Calcul des taux pour la sous-compétence
        $scTotals['taux_ua'] = ($scTotals['total_prevu_ua'] > 0) ? ($scTotals['nb_courant'] / $scTotals['total_prevu_ua']) * 100 : 0;
        $scTotals['taux_trimestre'] = ($scTotals['total_trimestre'] > 0) ? ($scTotals['nb_couverts_trimestre'] / $scTotals['total_trimestre']) * 100 : 0;
        $scTotals['taux_annee'] = ($scTotals['total_annee'] > 0) ? ($scTotals['nb_couverts_annee'] / $scTotals['total_annee']) * 100 : 0;

        // 3. Recalculer les totaux globaux (pour toute la classe)
        $allLecons = Lecon::whereIn('sous_competence_id', $this->getSousCompetencesForClasse($classe->id)->pluck('id'))->get();
        $globalTotals = [
            'total_prevu_ua' => 0,
            'nb_courant' => 0,
            'total_trimestre' => 0,
            'nb_couverts_trimestre' => 0,
            'total_annee' => 0,
            'nb_couverts_annee' => 0,
            'taux_ua' => 0,
            'taux_trimestre' => 0,
            'taux_annee' => 0
        ];

        foreach ($allLecons as $leconGlob) {
            $heuresPrevuesCol = 'total_a_couvrir_ua' . $evaluation->numero_eval;
            $heures = $leconGlob->{$heuresPrevuesCol} ?? 0;
            $stats = $this->calculateStatsForLecon($leconGlob, $evaluation, $allEvaluations, $heures);
            $globalTotals['total_prevu_ua'] += $stats['total_a_couvrir_ua'];
            $globalTotals['nb_courant'] += $stats['nb_courant'];
            $globalTotals['total_trimestre'] += $stats['total_trimestre'];
            $globalTotals['nb_couverts_trimestre'] += $stats['nb_couverts_trimestre'];
            $globalTotals['total_annee'] += $stats['total_annee'];
            $globalTotals['nb_couverts_annee'] += $stats['nb_couverts_annee'];
        }

        // Calcul des taux pour les totaux globaux
        $globalTotals['taux_ua'] = ($globalTotals['total_prevu_ua'] > 0) ? ($globalTotals['nb_courant'] / $globalTotals['total_prevu_ua']) * 100 : 0;
        $globalTotals['taux_trimestre'] = ($globalTotals['total_trimestre'] > 0) ? ($globalTotals['nb_couverts_trimestre'] / $globalTotals['total_trimestre']) * 100 : 0;
        $globalTotals['taux_annee'] = ($globalTotals['total_annee'] > 0) ? ($globalTotals['nb_couverts_annee'] / $globalTotals['total_annee']) * 100 : 0;

        return response()->json([
            'success' => true,
            'leconStats' => $leconStats,
            'scTotals' => $scTotals,
            'globalTotals' => $globalTotals
        ]);
    }

    /**
     * Génération du PDF de la fiche de couverture.
     */
    public function exportPdf($classe_id, $evaluation_id)
    {
        $classe = Classe::with('niveau', 'session')->findOrFail($classe_id);
        $evaluation = Evaluation::findOrFail($evaluation_id);

        $pdfData = $this->prepareCouvertureData($classe, $evaluation);
        $pdfData['is_pdf'] = true; // Indiquer à la vue que c'est un rendu PDF
        
        $pdf = Pdf::loadView('couverture.show_new', $pdfData)->setPaper('a4', 'landscape');

        return $pdf->download('fiche_couverture_' . $classe->nom . '_' . $evaluation->nom . '.pdf');
    }

    /**
     * Export Excel de la fiche de couverture (UA courante).
     */
    public function exportExcel($classe_id, $evaluation_id)
    {
        $classe = Classe::with('niveau', 'session')->findOrFail($classe_id);
        $evaluation = Evaluation::findOrFail($evaluation_id);
        $data = $this->prepareCouvertureData($classe, $evaluation);
        $export = new \App\Exports\CouvertureExport(
            $classe,
            $evaluation,
            $data['sousCompetences'],
            collect($data['lecons']),
            $data['leconStats'],
            $data['totalParDiscipline']
        );
        $filename = 'fiche_couverture_' . $classe->nom . '_UA' . ($evaluation->numero_eval ?? $evaluation->id) . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    /**
     * Prépare les données pour la vue show et l'export PDF.
     */
    private function prepareCouvertureData(Classe $classe, Evaluation $evaluation)
    {
        $sousCompetences = $this->getSousCompetencesForClasse($classe->id);
        $lecons = Lecon::whereIn('sous_competence_id', $sousCompetences->pluck('id'))->orderBy('nom')->get();
        $leconsBySousCompetence = $lecons->groupBy('sous_competence_id');

        $leconStats = [];
        $totalParDiscipline = [];
        $allEvaluations = $this->getEvaluationsForClasse($classe->id);

        foreach ($leconsBySousCompetence as $sc_id => $leconsInSc) {
            
            if (!isset($totalParDiscipline[$sc_id])) {
                $totalParDiscipline[$sc_id] = [
                    'total_prevu_ua' => 0,
                    'nb_courant' => 0,
                    'nb_non_couverts_precedent' => 0,
                    'total_trimestre' => 0,
                    'nb_couverts_trimestre' => 0,
                    'total_annee' => 0,
                    'nb_couverts_annee' => 0,
                    'taux_ua' => 0,
                    'taux_trimestre' => 0,
                    'taux_annee' => 0
                ];
            }

            foreach ($leconsInSc as $lecon) {
                // Correction: Récupérer les heures prévues depuis la bonne colonne de la table `lecons`.
                $heuresPrevuesUaCol = 'total_a_couvrir_ua' . $evaluation->numero_eval;
                $heuresPrevues = $lecon->{$heuresPrevuesUaCol} ?? 0;

                // Passer la valeur correcte à la fonction de calcul.
                $stats = $this->calculateStatsForLecon($lecon, $evaluation, $allEvaluations, $heuresPrevues);
                $leconStats[$lecon->id] = $stats;
                


                // Agrégation des totaux
                $totalParDiscipline[$sc_id]['total_prevu_ua'] += $stats['total_a_couvrir_ua'];
                $totalParDiscipline[$sc_id]['nb_courant'] += $stats['nb_courant'];
                $totalParDiscipline[$sc_id]['nb_non_couverts_precedent'] += $stats['nb_non_couverts_precedent'];
                $totalParDiscipline[$sc_id]['total_trimestre'] += $stats['total_trimestre'];
                $totalParDiscipline[$sc_id]['nb_couverts_trimestre'] += $stats['nb_couverts_trimestre'];
                $totalParDiscipline[$sc_id]['total_annee'] += $stats['total_annee'];
                $totalParDiscipline[$sc_id]['nb_couverts_annee'] += $stats['nb_couverts_annee'];
            }

            // Calcul des taux pour la sous-compétence
            $sc_totals = &$totalParDiscipline[$sc_id];
            $sc_totals['taux_ua'] = ($sc_totals['total_prevu_ua'] > 0) ? ($sc_totals['nb_courant'] / $sc_totals['total_prevu_ua']) * 100 : 0;
            $sc_totals['taux_trimestre'] = ($sc_totals['total_trimestre'] > 0) ? ($sc_totals['nb_couverts_trimestre'] / $sc_totals['total_trimestre']) * 100 : 0;
            $sc_totals['taux_annee'] = ($sc_totals['total_annee'] > 0) ? ($sc_totals['nb_couverts_annee'] / $sc_totals['total_annee']) * 100 : 0;
        }


        return [
            'classe' => $classe,
            'evaluation' => $evaluation,
            'sousCompetences' => $sousCompetences,
            'lecons' => $leconsBySousCompetence,
            'leconStats' => $leconStats,
            'totalParDiscipline' => $totalParDiscipline,
        ];
    }

    // ... (Helper methods for data retrieval and calculation) ...


    private function calculateStatsForLecon(Lecon $lecon, Evaluation $current_evaluation, $all_evaluations, $heures_prevues_ua)
    {
        $stats = [];

        // 1. Heures et taux pour l'UA en cours
        // Correction : on utilise bien la colonne de l'UA courante
        $col = 'total_a_couvrir_ua' . $current_evaluation->numero_eval;
        $heures_prevues_ua = $lecon->$col ?? 0;
        $stats['total_a_couvrir_ua'] = $heures_prevues_ua;
        $couverture_actuelle = Couverture::where('lecon_id', $lecon->id)
            ->where('evaluation_id', $current_evaluation->id)
            ->where('classe_id', $current_evaluation->classe_id)
            ->first();
        $stats['nb_courant'] = $couverture_actuelle->nb_couverts ?? 0;
        $stats['taux_ua'] = ($heures_prevues_ua > 0) ? ($stats['nb_courant'] / $heures_prevues_ua) * 100 : 0;

        // 2. Calcul des non-couverts des UA précédentes (même trimestre)
        $evaluations_precedentes = $all_evaluations->where('trimestre', $current_evaluation->trimestre)
            ->where('numero_eval', '<', $current_evaluation->numero_eval);
        
        $heures_prevues_precedentes = 0;
        foreach ($evaluations_precedentes as $eval_prec) {
            $col = 'total_a_couvrir_ua' . $eval_prec->numero_eval;
            $heures_prevues_precedentes += $lecon->{$col} ?? 0;
        }
        
        $heures_couvertes_precedentes = Couverture::where('lecon_id', $lecon->id)
            ->whereIn('evaluation_id', $evaluations_precedentes->pluck('id'))
            ->where('classe_id', $current_evaluation->classe_id)
            ->sum('nb_couverts');

        $stats['nb_non_couverts_precedent'] = max(0, $heures_prevues_precedentes - $heures_couvertes_precedentes);

        // 3. Calcul des totaux et taux pour le TRIMESTRE
        $evaluations_trimestre = $all_evaluations->where('trimestre', $current_evaluation->trimestre);
        $total_heures_trimestre = 0;
        foreach ($evaluations_trimestre as $eval_trim) {
            $col = 'total_a_couvrir_ua' . $eval_trim->numero_eval;
            $total_heures_trimestre += $lecon->{$col} ?? 0;
        }
        $stats['total_trimestre'] = $total_heures_trimestre;

        $nb_couverts_trimestre = Couverture::where('lecon_id', $lecon->id)
            ->whereIn('evaluation_id', $evaluations_trimestre->pluck('id'))
            ->where('classe_id', $current_evaluation->classe_id)
            ->sum('nb_couverts');
        $stats['nb_couverts_trimestre'] = $nb_couverts_trimestre;
        \Log::debug('[Couverture] Stats Leçon', [
            'lecon_id' => $lecon->id,
            'trimestre' => $current_evaluation->trimestre,
            'evals_trim' => $evaluations_trimestre->pluck('id')->toArray(),
            'nb_couverts_trimestre' => $nb_couverts_trimestre,
            'nb_couverts_annee' => null // sera ajouté plus bas
        ]);
        $stats['taux_trimestre'] = ($stats['total_trimestre'] > 0) ? ($nb_couverts_trimestre / $stats['total_trimestre']) * 100 : 0;

        // 4. Calcul des totaux et taux pour l'ANNEE
        $total_heures_annee = 0;
        foreach ($all_evaluations as $eval_annee) {
            $col = 'total_a_couvrir_ua' . $eval_annee->numero_eval;
            $total_heures_annee += $lecon->{$col} ?? 0;
        }
        $stats['total_annee'] = $total_heures_annee;

        $nb_couverts_annee = Couverture::where('lecon_id', $lecon->id)
            ->whereIn('evaluation_id', $all_evaluations->pluck('id'))
            ->where('classe_id', $current_evaluation->classe_id)
            ->sum('nb_couverts');
        $stats['nb_couverts_annee'] = $nb_couverts_annee;
        \Log::debug('[Couverture] Stats Leçon (année)', [
            'lecon_id' => $lecon->id,
            'evals_annee' => $all_evaluations->pluck('id')->toArray(),
            'nb_couverts_annee' => $nb_couverts_annee
        ]);
        $stats['taux_annee'] = ($stats['total_annee'] > 0) ? ($nb_couverts_annee / $stats['total_annee']) * 100 : 0;

        return $stats;
    }

    private function getEvaluationsForClasse($classe_id)
    {
        // Les évaluations sont directement liées à la classe.
        return \App\Models\Evaluation::where('classe_id', $classe_id)
            ->orderBy('trimestre')
            ->orderBy('numero_eval')
            ->get();
    }

    private function getSousCompetencesForClasse($classe_id)
    {
        $classe = \App\Models\Classe::findOrFail($classe_id);
        // On récupère les sous-compétences via le niveau de la classe.
        // Classe -> Niveau -> Competences -> SousCompetences
        return \App\Models\SousCompetence::whereHas('competence.niveau', function ($query) use ($classe) {
            $query->where('id', $classe->niveau_id);
        })->get();
    }

    /**
     * Affiche le tableau récapitulatif des totaux de couverture.
     */
    public function showRecapitulatif(\App\Models\Classe $classe)
    {
        $data = $this->getRecapitulatifData($classe);
        $data['session'] = $classe->session; // Correction : ajoute la session pour la vue web
        return view('couverture.recap', $data);
    }

    /**
     * Exporte le tableau récapitulatif en PDF.
     */
    public function exportRecapPdf(\App\Models\Classe $classe)
    {
        $data = $this->getRecapitulatifData($classe);
        $data['is_pdf'] = true; // Signale à la vue de générer un PDF
        $data['session'] = $classe->session; // Correction : ajoute la session pour la vue

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('couverture.recap', $data)->setPaper('a4', 'landscape');

        return $pdf->stream('recapitulatif_couverture_' . $classe->nom . '.pdf');
    }

    /**
     * Export Excel du récapitulatif de couverture.
     */
    public function exportRecapExcel(\App\Models\Classe $classe)
    {
        $data = $this->getRecapitulatifData($classe);
        $export = new \App\Exports\RecapitulatifCouvertureExport(
            $classe,
            $classe->session,
            $data['recapData'],
            $data['uasByTrimestre']
        );
        $filename = 'recapitulatif_couverture_' . $classe->nom . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    /**
     * Méthode privée pour préparer les données du récapitulatif.
     * Centralise la logique pour éviter la duplication de code.
     */
    private function getRecapitulatifData(\App\Models\Classe $classe)
    {
        $evaluations = $this->getEvaluationsForClasse($classe->id);
        $sousCompetences = $this->getSousCompetencesForClasse($classe->id);

        $lecons = \App\Models\Lecon::with('sousCompetence')
            ->whereIn('sous_competence_id', $sousCompetences->pluck('id'))
            ->orderBy('sous_competence_id')
            ->orderBy('nom')
            ->get();

        $couvertures = \App\Models\Couverture::where('classe_id', $classe->id)
            ->get()
            ->keyBy(function ($item) {
                return $item['lecon_id'] . '-' . $item['evaluation_id'];
            });

        $recapData = [];
        foreach ($lecons as $lecon) {
            $leconTotaux = [];
            $trimestreTotals = [1 => 0, 2 => 0, 3 => 0];

            foreach ($evaluations as $evaluation) {
                $key = $lecon->id . '-' . $evaluation->id;
                $nbCouverts = $couvertures->get($key)->nb_couverts ?? 0;
                $leconTotaux['ua_' . $evaluation->numero_eval] = $nbCouverts;
                if (isset($trimestreTotals[$evaluation->trimestre])) {
                    $trimestreTotals[$evaluation->trimestre] += $nbCouverts;
                }
            }

            $totalAnnuel = array_sum($trimestreTotals);
            foreach ($trimestreTotals as $trimestre => $total) {
                $leconTotaux['trimestre_' . $trimestre] = $total;
            }
            // CORRECTION : Utilisation de la clé 'annuel' attendue par la vue.
            $leconTotaux['annuel'] = $totalAnnuel;

            if (!isset($recapData[$lecon->sous_competence_id])) {
                $recapData[$lecon->sous_competence_id] = [
                    'nom' => $lecon->sousCompetence->nom,
                    'lecons' => []
                ];
            }

            $recapData[$lecon->sous_competence_id]['lecons'][] = [
                'nom' => $lecon->nom,
                'id' => $lecon->id,
                'totaux' => $leconTotaux
            ];
        }

        $trimestres = $evaluations->pluck('trimestre')->unique()->sort();
        $uasByTrimestre = $evaluations->groupBy('trimestre');

        return compact('classe', 'evaluations', 'recapData', 'trimestres', 'uasByTrimestre');
    }
}
