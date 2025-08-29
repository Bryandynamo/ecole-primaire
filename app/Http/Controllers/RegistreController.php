<?php

namespace App\Http\Controllers;

require_once __DIR__.'/AppreciationHelper.php';

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Session;
use App\Models\Eleve;
use App\Models\SousCompetence;
use App\Models\Modalite;
use App\Models\Note;
use App\Models\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Services\BulletinOptimizationService;
use Illuminate\Support\Facades\Cache;

class RegistreController extends Controller
{
    // Statistiques du registre
    public function statistiques($session_id, $classe_id, $periode = 1, $type = 'ua')
    {
        try {
            if (!$session_id || !$classe_id) {
                return redirect()->back()->with('error', 'Paramètres manquants');
            }

            $session = Session::findOrFail($session_id);
            // Charger les relations de manière robuste pour éviter les erreurs critiques
            $classe = Classe::with([
                'eleves' => function ($query) {
                    $query->orderBy('nom');
                },
                'niveau.competences.sousCompetences.modalites' => function ($query) {
                    $query->withPivot('points_max')->orderBy('nom');
                }
            ])->findOrFail($classe_id);

            // Gestion spéciale pour les stats annuelles
            if ($periode === 'annee') {
                $statsUAs = [];
                $statistiquesData = $this->getStatistiquesData($session_id, $classe_id, $periode, $type);
                return view('registre.statistiques', array_merge($statistiquesData, [
                    'session' => $session,
                    'classe' => $classe,
                    'periode' => $periode,
                    'type' => $type,
                    'statsUAs' => [],
                ]));
            }

            // Déterminer l'évaluation à utiliser
            $evaluation = $periode;
            if ($type === 'trimestre') {
                $evaluation = ($periode - 1) * 3 + 1;
            }

            // Trouver l'ID réel de l'évaluation
            $evaluationModel = \App\Models\Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->where('numero_eval', $evaluation)
                ->first();
            
            if (!$evaluationModel) {
                return redirect()->back()->with('error', 'Évaluation non trouvée');
            }

            $evaluationId = $evaluationModel->id;

            // Si c'est un trimestre, calculer les stats pour chaque UA
            $statsUAs = [];
            if ($type === 'trimestre') {
                $debutUA = ($periode - 1) * 3 + 1;
                $finUA = $periode * 3;
                for ($ua = $debutUA; $ua <= $finUA; $ua++) {
                    Cache::forget("statistics_{$session_id}_{$classe_id}_{$ua}");
                    // Trouver l'évaluation pour cette UA
                    $evalUA = \App\Models\Evaluation::where('classe_id', $classe_id)
                        ->where('session_id', $session_id)
                        ->where('numero_eval', $ua)
                        ->first();
                    if ($evalUA) {
                        // Récupérer les notes pour cette UA
                        $notesUA = Note::where('session_id', $session_id)
                            ->where('classe_id', $classe_id)
                            ->where('evaluation_id', $evalUA->id)
                            ->get();
                        // Calculer les stats pour cette UA
                        $statsUA = $this->calculateStatsForNotes($notesUA, $classe);
                        $statsUAs[$ua] = $statsUA;
                    } else {
                        $statsUAs[$ua] = null;
                    }
                }
            }

            // Récupérer l'évaluation principale pour les statistiques globales
            // IMPORTANT: pour un trimestre, on utilise UNIQUEMENT la DERNIÈRE UA du trimestre
            // (UA 3 pour T1, UA 6 pour T2, UA 9 pour T3). Pour 'ua', on prend $periode tel quel.
            $targetNumeroEval = ($type === 'trimestre') ? ($periode * 3) : $periode;
            $evaluation = Evaluation::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->where('numero_eval', $targetNumeroEval)
                ->firstOrFail();

            // Supprimer le cache pour cette statistique avant calcul
            Cache::forget("statistics_{$session_id}_{$classe_id}_{$periode}_{$type}");

            // FILTRAGE STRICT : On ne prend que les notes de cette évaluation exacte.
            $notes = Note::where('evaluation_id', $evaluation->id)
                ->whereIn('eleve_id', $classe->eleves->pluck('id'))
                ->get();

            // Calculer les statistiques principales
            $statistiques = $this->calculateStatsForNotes($notes, $classe);

            return view('registre.statistiques', compact('statistiques', 'session', 'classe', 'periode', 'type', 'statsUAs'));

        } catch (\Exception $e) {
            \Log::error('Erreur dans la méthode statistiques', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Une erreur est survenue lors du calcul des statistiques: ' . $e->getMessage());
        }
    }

    public function exportStatistiquesPdf(Request $request)
    {
        $session_id = $request->input('session_id');
        $classe_id = $request->input('classe_id');
        $periode = $request->input('periode');
        $type = $request->input('type');

        $data = $this->getStatistiquesData($session_id, $classe_id, $periode, $type);
        
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('registre.statistiques_pdf', $data)->setPaper('a4', 'landscape');

        $fileName = "statistiques_{$data['classe']->nom}_{$type}_{$periode}.pdf";

        return $pdf->stream($fileName);
    }

    /**
     * Export Excel des statistiques (UA / trimestre / année)
     */
    public function exportStatistiquesExcel(Request $request)
    {
        $session_id = $request->input('session_id');
        $classe_id = $request->input('classe_id');
        $periode = $request->input('periode');
        $type = $request->input('type');

        \Log::info('[STATS_EXCEL] Params reçus', compact('session_id','classe_id','periode','type'));
        $data = $this->getStatistiquesData($session_id, $classe_id, $periode, $type);
        \Log::info('[STATS_EXCEL] Clés data', ['keys' => array_keys($data)]);
        \Log::info('[STATS_EXCEL] Aperçu statistiques', [
            'inscrits' => $data['statistiques']['inscrits'] ?? null,
            'ayant_compose' => $data['statistiques']['ayant_compose'] ?? null,
            'moyenne_generale' => $data['statistiques']['moyenne_generale'] ?? null,
        ]);

        $export = new \App\Exports\StatistiquesExport(
            $data['classe'],
            $data['session'],
            $data['periode'],
            $data['type'],
            $data['statistiques'],
            $data['statsUAs'] ?? [],
            $data['recapEvaluations'] ?? []
        );

        $filename = 'statistiques_'.$data['classe']->nom.'_'.$data['type'].'_'.$data['periode'].'.xlsx';
        \Log::info('[STATS_EXCEL] Téléchargement Excel', ['filename' => $filename]);
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    private function getStatistiquesData($session_id, $classe_id, $periode, $type)
    {
        \Log::info('[STATS] Appel getStatistiquesData', compact('session_id','classe_id','periode','type'));
        
        $session = Session::findOrFail($session_id);
        $classe = Classe::with(['eleves' => function ($query) {
            $query->orderBy('nom');
        }])->findOrFail($classe_id);

        $statsUAs = [];
        $notes = collect();

        if ($periode === 'annee') {
    // Statistiques annuelles : toutes les évaluations de la classe/session
    $evaluationIds = Evaluation::where('session_id', $session_id)
        ->where('classe_id', $classe_id)
        ->pluck('id');
    $recapEvaluations = [];
    if ($evaluationIds->isNotEmpty()) {
        $notes = Note::whereIn('evaluation_id', $evaluationIds)
            ->whereIn('eleve_id', $classe->eleves->pluck('id'))
            ->get();
        // Calcul du récapitulatif par évaluation
        $evals = Evaluation::whereIn('id', $evaluationIds)->orderBy('numero_eval')->get();
        foreach ($evals as $eval) {
            $notesEval = $notes->where('evaluation_id', $eval->id);
            $statsEval = $this->calculateStatsForNotes($notesEval, $classe);
            $statsUAs[$eval->numero_eval] = $statsEval;
            $recapEvaluations[] = [
                'numero_eval' => $eval->numero_eval,
                'stats' => $statsEval
            ];
        }
        ksort($statsUAs);
    }
    // --- STATISTIQUES ANNUELLES ---
    if ($periode === 'annee') {
        // Récupérer uniquement les dernières UAs de chaque trimestre (3, 6, 9)
        $trimestres = [1, 2, 3];
        $lastEvaluationIds = collect();
        
        foreach ($trimestres as $trimestre) {
            $finUA = $trimestre * 3; // UA 3, 6, 9
            $lastEval = Evaluation::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->where('numero_eval', $finUA)
                ->first();
            if ($lastEval) {
                $lastEvaluationIds->push($lastEval->id);
            }
        }
        
        // Statistiques basées UNIQUEMENT sur les dernières UAs de chaque trimestre
        $notes = $lastEvaluationIds->isNotEmpty()
            ? Note::whereIn('evaluation_id', $lastEvaluationIds)
                ->whereIn('eleve_id', $classe->eleves->pluck('id'))
                ->get()
            : collect();
            
        $statistiques = $this->calculateStatsForNotes($notes, $classe);
        return compact('statistiques', 'session', 'classe', 'periode', 'type', 'statsUAs', 'recapEvaluations');
    }

    // --- STATISTIQUES TRIMESTRIELLES ---
    if ($type === 'trimestre') {
        $debutUA = ($periode - 1) * 3 + 1;
        $finUA = $periode * 3;
        
        // Récapitulatif de toutes les UAs du trimestre (pour affichage)
        for ($ua = $debutUA; $ua <= $finUA; $ua++) {
            $evalUA = Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->where('numero_eval', $ua)
                ->first();

            if ($evalUA) {
                $notesUA = Note::where('evaluation_id', $evalUA->id)
                    ->whereIn('eleve_id', $classe->eleves->pluck('id'))
                    ->get();
                $statsUAs[$ua] = $this->calculateStatsForNotes($notesUA, $classe);
                $recapEvaluations[] = [
                    'numero_eval' => $ua,
                    'stats' => $statsUAs[$ua]
                ];
            } else {
                $statsUAs[$ua] = $this->getEmptyStats($classe);
            }
        }
        
        // STATISTIQUES FINALES : UNIQUEMENT la dernière UA du trimestre
        $lastEval = Evaluation::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->where('numero_eval', $finUA) // Dernière UA exacte
            ->first();
            
        if ($lastEval) {
            \Log::info('[STATS] DEBUG UA TRIMESTRE', [
                'trimestre' => $periode,
                'numero_eval' => $lastEval->numero_eval,
                'evaluation_id' => $lastEval->id
            ]);
            
        }
        $notes = $lastEval
            ? Note::where('evaluation_id', $lastEval->id)
                ->whereIn('eleve_id', $classe->eleves->pluck('id'))
                ->get()
            : collect();
            
        $statistiques = $this->calculateStatsForNotes($notes, $classe);
        return compact('statistiques', 'session', 'classe', 'periode', 'type', 'statsUAs', 'recapEvaluations');
    }

    // --- AUTRES CAS (UA) ---
    $statistiques = $this->calculateStatsForNotes($notes, $classe);
    return compact('statistiques', 'session', 'classe', 'periode', 'type', 'statsUAs', 'recapEvaluations');
}


        $statistiques = $this->calculateStatsForNotes($notes, $classe);

        return compact('statistiques', 'session', 'classe', 'periode', 'type', 'statsUAs');
    }

    private function getEmptyStats(Classe $classe)
    {
        $inscrits = $classe->eleves->count();
        $inscrits_garcons = $classe->eleves->where('sexe', 'M')->count();
        $inscrits_filles = $classe->eleves->where('sexe', 'F')->count();

        return [
            'inscrits' => $inscrits,
            'inscrits_garcons' => $inscrits_garcons,
            'inscrits_filles' => $inscrits_filles,
            'ayant_compose' => 0,
            'ayant_compose_garcons' => 0,
            'ayant_compose_filles' => 0,
            'admis' => 0,
            'admis_garcons' => 0,
            'admis_filles' => 0,
            'echoues' => 0,
            'echoues_garcons' => 0,
            'echoues_filles' => 0,
            'moyenne_generale' => 0,
            'moyenne_generale_garcons' => 0,
            'moyenne_generale_filles' => 0,
            'pourc_reussite' => 0,
            'pourc_echec' => 0,
            'pourc_reussite_garcons' => 0,
            'pourc_echec_garcons' => 0,
            'pourc_reussite_filles' => 0,
            'pourc_echec_filles' => 0,
            'moyenne_premier' => '-',
            'moyenne_dernier' => '-',
            'moyenne_premier_garcons' => '-',
            'moyenne_dernier_garcons' => '-',
            'moyenne_premier_filles' => '-',
            'moyenne_dernier_filles' => '-',
        ];
    }

    // Méthode helper pour calculer les stats à partir des notes
    /**
     * Calcule les statistiques d'une classe pour une évaluation donnée.
     * - Chaque moyenne individuelle est calculée sur la somme réelle des points max de la classe (toutes sous-compétences/modalités attendues).
     * - Les statistiques sont strictement alignées avec la logique du registre (aucune note absente ou nulle n'est prise en compte pour "ayant composé").
     */
    private function calculateStatsForNotes($notes, $classe)
    {
        // 1. Récupérer tous les couples sous-compétence/modalité et leur barème (points_max)
        $pointsMaxMap = \App\Models\SousCompetenceModalite::all()
            ->keyBy(function ($item) {
                return $item->sous_competence_id . '-' . $item->modalite_id;
            })
            ->map(function ($item) {
                return $item->points_max;
            });
        $combosClasse = [];
        foreach ($classe->niveau->competences as $competence) {
            foreach ($competence->sousCompetences as $sc) {
                foreach ($sc->modalites as $modalite) {
                    $key = $sc->id . '-' . $modalite->id;
                    $combosClasse[$key] = $pointsMaxMap->get($key, 20); // 20 si barème manquant
                }
            }
        }
        $totalPointsMaxClasse = array_sum($combosClasse);
        if ($totalPointsMaxClasse === 0) {
            \Log::warning('[STATS] Aucun barème défini pour la classe', ['classe_id' => $classe->id]);
            return [];
        }
        $eleves = $classe->eleves;
        $inscrits = $eleves->count();
        $inscrits_garcons = $eleves->where('sexe', 'M')->count();
        $inscrits_filles = $eleves->where('sexe', 'F')->count();
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
        $debug_eleves = [];
        foreach ($eleves as $eleve) {

            // CORRECTION : On vérifie si l'élève a au moins une note pour être considéré comme "ayant composé"
            $notesEleveCollection = $notes->where('eleve_id', $eleve->id);
            if ($notesEleveCollection->isEmpty()) {
                continue; // On passe à l'élève suivant s'il n'a aucune note
            }

            // Si on arrive ici, l'élève a bien composé. On incrémente les compteurs.
            $ayant_compose++;
            if ($eleve->sexe === 'M') {
                $ayant_compose_garcons++;
            } else if ($eleve->sexe === 'F') {
                $ayant_compose_filles++;
            }

            $totalPoints = 0;
            $notesDebug = [];
            $notesEleve = [];
            
            // Pour chaque couple attendu (barème global), prendre la note si elle existe, sinon 0
            foreach ($combosClasse as $key => $pointsMax) {
                [$scid, $modid] = explode('-', $key);
                $note = $notesEleveCollection->where('sous_competence_id', $scid)
                                              ->where('modalite_id', $modid)
                                              ->first();
                $valeur = ($note && is_numeric($note->valeur)) ? (float)$note->valeur : 0;
                $totalPoints += $valeur;
                $notesEleve[$key] = $valeur; // Stocker pour le debug
            }
            
            // Calcul de la moyenne individuelle sur le barème global (NON arrondie pour la moyenne générale)
            $moyenne_non_arrondie = ($totalPointsMaxClasse > 0) ? ($totalPoints / $totalPointsMaxClasse) * 20 : 0;
            $moyennes[] = $moyenne_non_arrondie;
            $moyenne = round($moyenne_non_arrondie, 2);
            
            // Log détaillé pour débogage
            \Log::info('[DEBUG_ELEVE] Notes pour ' . $eleve->nom . ' ' . $eleve->prenom, [
                'notes' => $notesEleve,
                'total_points' => $totalPoints,
                'moyenne_non_arrondie' => $moyenne_non_arrondie,
                'moyenne_arrondie' => $moyenne
            ]);
            if ($moyenne >= 10) {
                $admis++;
            } else {
                $echoues++;
            }
            if ($eleve->sexe === 'M') {
                $moyennes_garcons[] = $moyenne;
                if ($moyenne >= 10) {
                    $admis_garcons++;
                } else {
                    $echoues_garcons++;
                }
            } else if ($eleve->sexe === 'F') {
                $moyennes_filles[] = $moyenne;
                if ($moyenne >= 10) {
                    $admis_filles++;
                } else {
                    $echoues_filles++;
                }
            }
            $debug_eleves[] = [
                'nom' => $eleve->nom,
                'prenom' => $eleve->prenom,
                'sexe' => $eleve->sexe,
                'notes' => $notesDebug,
                'totalPoints' => $totalPoints,
                'moyenne' => $moyenne,
                'aCompose' => true
            ];
        }
        \Log::info('[DEBUG_STATS] Calculs par élève', $debug_eleves);
        \Log::info('[DEBUG_STATS] Tableaux des moyennes', ['moyennes' => $moyennes, 'moyennes_garcons' => $moyennes_garcons, 'moyennes_filles' => $moyennes_filles]);
        // Calcul des moyennes et taux globaux pour la vue Blade
        $moyenne_generale = count($moyennes) > 0 ? round(array_sum($moyennes) / count($moyennes), 2) : 0;
        $moyenne_generale_garcons = count($moyennes_garcons) > 0 ? round(array_sum($moyennes_garcons) / count($moyennes_garcons), 2) : 0;
        $moyenne_generale_filles = count($moyennes_filles) > 0 ? round(array_sum($moyennes_filles) / count($moyennes_filles), 2) : 0;
        $pourc_reussite = $ayant_compose > 0 ? round($admis * 100 / $ayant_compose, 2) : 0;
        $pourc_echec = $ayant_compose > 0 ? round($echoues * 100 / $ayant_compose, 2) : 0;
        $pourc_reussite_garcons = $ayant_compose_garcons > 0 ? round($admis_garcons * 100 / $ayant_compose_garcons, 2) : 0;
        $pourc_echec_garcons = $ayant_compose_garcons > 0 ? round($echoues_garcons * 100 / $ayant_compose_garcons, 2) : 0;
        $pourc_reussite_filles = $ayant_compose_filles > 0 ? round($admis_filles * 100 / $ayant_compose_filles, 2) : 0;
        $pourc_echec_filles = $ayant_compose_filles > 0 ? round($echoues_filles * 100 / $ayant_compose_filles, 2) : 0;
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
            'moyenne_generale' => $moyenne_generale,
            'moyenne_generale_garcons' => $moyenne_generale_garcons,
            'moyenne_generale_filles' => $moyenne_generale_filles,
            'pourc_reussite' => $pourc_reussite,
            'pourc_echec' => $pourc_echec,
            'pourc_reussite_garcons' => $pourc_reussite_garcons,
            'pourc_echec_garcons' => $pourc_echec_garcons,
            'pourc_reussite_filles' => $pourc_reussite_filles,
            'pourc_echec_filles' => $pourc_echec_filles,
            'moyenne_premier' => count($moyennes) > 0 ? round(max($moyennes) ?? 0, 2) : '-', // Moyenne du premier arrondie
            'moyenne_premier_exacte' => count($moyennes) > 0 ? max($moyennes) ?? 0 : '-', // Moyenne du premier non arrondie
            'debug_moyennes' => [
                'toutes_moyennes' => $moyennes,
                'moyenne_max' => count($moyennes) > 0 ? max($moyennes) ?? 0 : '-'
            ],
            'moyenne_dernier' => (count($moyennes) > 0 ? (
                ($nonzero = array_filter($moyennes, function($m) { return $m > 0; })) ? round(min($nonzero), 2) : 0
            ) : '-'),
            'moyenne_premier_garcons' => count($moyennes_garcons) > 0 ? round(max($moyennes_garcons) ?? 0, 2) : '-',
            'moyenne_dernier_garcons' => (count($moyennes_garcons) > 0 ? (
                ($nonzero = array_filter($moyennes_garcons, function($m) { return $m > 0; })) ? round(min($nonzero), 2) : 0
            ) : '-'),
            'moyenne_premier_filles' => count($moyennes_filles) > 0 ? round(max($moyennes_filles), 2) : '-',
            'moyenne_dernier_filles' => (count($moyennes_filles) > 0 ? (
                ($nonzero = array_filter($moyennes_filles, function($m) { return $m > 0; })) ? round(min($nonzero), 2) : 0
            ) : '-'),
            'debug_eleves' => $debug_eleves,
        ];
        // Les tableaux $moyennes, $moyennes_garcons, $moyennes_filles ne contiennent que les ayant composé.

    }

    // Méthode pour obtenir les sous-competences d'une compétence
    protected function getSousCompetences($competenceLabel)
    {
        $comp = SousCompetence::where('competence_id', $competenceLabel)->get();
        return $comp;
    }

    // Méthode pour obtenir les modalités d'une sous-competence
    protected function getModalites($scLabel)
    {
        $sc = SousCompetence::where('id', $scLabel)->first();
        return $sc->modalites;
    }

    // Répertoire des registres filtrable
    public function repertoire(Request $request)
    {
        // Restreindre à la session et la classe de l'enseignant connecté
        $enseignant = $request->attributes->get('enseignant')
            ?: \App\Models\Enseignant::where('user_id', auth()->id())->first();

        if (!$enseignant) {
            return redirect()->route('login')->with('error', "Profil enseignant introuvable");
        }

        $sessions = Session::where('id', $enseignant->session_id)->get();
        $classes = Classe::where('id', $enseignant->classe_id)->get();
        $evaluations = [1,2,3];
        $selectedSession = $request->session_id ?: $enseignant->session_id;
        $selectedClasse = $request->classe_id ?: $enseignant->classe_id;
        $selectedEvaluation = $request->evaluation;
        $registres = [];
        if ($selectedSession && $selectedClasse && $selectedEvaluation) {
            // On cherche d'abord les élèves
            $eleves = Eleve::where('classe_id', $selectedClasse)
                ->where('session_id', $selectedSession)
                ->get();
            if ($eleves->count() > 0) {
                // On génère la structure du registre même sans note
                $competences = Classe::find($selectedClasse)?->niveau?->competences()->with(['sousCompetences.modalites'])->get() ?? collect();
                foreach ($competences as $competence) {
                    foreach ($competence->sousCompetences as $sousCompetence) {
                        foreach ($sousCompetence->modalites as $modalite) {
                            $registres[] = (object)[
                                'sousCompetence' => $sousCompetence,
                                'modalite' => $modalite,
                                'sous_competence_id' => $sousCompetence->id,
                                'modalite_id' => $modalite->id,
                            ];
                        }
                    }
                }
            } else {
                // Ancienne logique : on cherche les notes
                $registres = Note::where('session_id', $selectedSession)
                    ->where('classe_id', $selectedClasse)
                    ->where('evaluation_id', $selectedEvaluation)
                    ->select('sous_competence_id','modalite_id')
                    ->distinct()
                    ->with(['sousCompetence','modalite'])
                    ->get();
            }
        }
        return view('registre.repertoire', compact('sessions','classes','evaluations','selectedSession','selectedClasse','selectedEvaluation','registres'));
    }

    // Formulaire de sélection registre
    public function index(Request $request)
    {
        // Restreindre à la session et la classe de l'enseignant connecté
        $enseignant = $request->attributes->get('enseignant')
            ?: \App\Models\Enseignant::where('user_id', auth()->id())->first();

        if (!$enseignant) {
            return redirect()->route('login')->with('error', "Profil enseignant introuvable");
        }

        $sessions = Session::where('id', $enseignant->session_id)->get();
        $classes = Classe::where('id', $enseignant->classe_id)->get();
        $selectedSession = $request->session_id ?: $enseignant->session_id;
        $selectedClasse = $request->classe_id ?: $enseignant->classe_id;
        $evaluations = collect();
        if ($selectedSession && $selectedClasse) {
            $evaluations = \App\Models\Evaluation::where('session_id', $selectedSession)
                ->where('classe_id', $selectedClasse)
                ->orderBy('numero_eval')
                ->get();
            \Log::info('[DEBUG] Evaluations trouvées', $evaluations->toArray());
            if ($evaluations->isEmpty()) {
                session()->flash('error', 'Aucune évaluation trouvée pour cette session et cette classe.');
            }
        }
        return view('registre.index', compact('sessions', 'classes', 'evaluations', 'selectedSession', 'selectedClasse'));
    }

    // Affichage du formulaire de saisie des notes
    public function saisie(Request $request)
    {
        if ($request->method() === 'GET') {
            return redirect()->route('registre.index');
        }

        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'classe_id' => 'required|exists:classes,id',
            'evaluation' => 'required|integer|min:1|max:3',
        ]);

        return $this->show($request->session_id, $request->classe_id, $request->evaluation);
    }

    // Impression PDF du registre
    public function exportPdf($session_id, $classe_id, $evaluation = null)
    {
        if ($evaluation === null) {
            $evaluation = request()->get('evaluation', 1);
        }
        
        // Trouver l'ID réel de l'évaluation
        $evaluationModel = \App\Models\Evaluation::where('classe_id', $classe_id)
            ->where('session_id', $session_id)
            ->where('numero_eval', $evaluation)
            ->first();
            
        if (!$evaluationModel) {
            return back()->with('error', 'Évaluation non trouvée');
        }
        
        $evaluationId = $evaluationModel->id;
        
        // Optimisation : Charger toutes les données en une seule fois
        $classe = Classe::with([
            'eleves' => function ($query) {
                $query->orderBy('nom');
            },
            'niveau.competences.sousCompetences.modalites' => function ($query) {
                $query->withPivot('points_max')->orderBy('nom');
            }
        ])->findOrFail($classe_id);
        
        $session = Session::select('id', 'nom')->findOrFail($session_id);
        
        // Récupérer les notes avec une seule requête optimisée
        $notes = Note::select('eleve_id', 'sous_competence_id', 'modalite_id', 'valeur')
            ->where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->where('evaluation_id', $evaluationId) // Utiliser l'ID réel de l'évaluation
            ->get()
            ->groupBy('eleve_id')
            ->map(function($notes, $eleveId) {
                return $notes->groupBy('sous_competence_id')
                    ->map(function($notes, $scId) {
                        return $notes->pluck('valeur', 'modalite_id')->toArray();
                    })->toArray();
            })->toArray();

        // Préparer l'ordre des compétences et sous-compétences
        $ordreCompetences = [];
        $pointsMaxMap = [];
        $sousCompetenceIds = [];
        $modaliteIds = [];
        
        foreach ($classe->niveau->competences as $competence) {
            $sousCompetences = [];
            foreach ($competence->sousCompetences as $sc) {
                if ($sc->modalites->isNotEmpty()) {
                    $modalitesData = [];
                    $pointsData = [];
                    $sousCompetenceIds[$sc->nom] = $sc->id;
                    
                    foreach ($sc->modalites as $modalite) {
                        $modalitesData[] = $modalite->nom;
                        $modaliteIds[$modalite->nom] = $modalite->id;
                        $pointsMax = $modalite->pivot->points_max ?? 20;
                        $pointsData[] = $pointsMax;
                        $pointsMaxMap[$sc->nom][$modalite->nom] = $pointsMax;
                    }
                    
                    $sousCompetences[] = [
                        'label' => $sc->nom,
                        'modalites' => $modalitesData,
                        'points_max' => $pointsData
                    ];
                }
            }
            if (!empty($sousCompetences)) {
                $ordreCompetences[] = [
                    'label' => $competence->nom,
                    'sous' => $sousCompetences
                ];
            }
        }

        // Calculer les moyennes et rangs des élèves
        $elevesAvecMoyennes = $classe->eleves->map(function($eleve) use ($notes, $pointsMaxMap, $sousCompetenceIds, $modaliteIds) {
            $totalPoints = 0;
            $totalPointsMax = 0;
            
            foreach($pointsMaxMap as $scLabel => $modalites) {
                $scId = $sousCompetenceIds[$scLabel] ?? null;
                if ($scId) {
                    foreach($modalites as $modaliteNom => $points) {
                        $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                        if ($modaliteId && isset($notes[$eleve->id][$scId][$modaliteId])) {
                            $note = $notes[$eleve->id][$scId][$modaliteId];
                            if (is_numeric($note)) {
                                $totalPoints += $note;
                            }
                        }
                        $totalPointsMax += $points;
                    }
                }
            }
            
            $moyenne = $totalPointsMax > 0 ? ($totalPoints / $totalPointsMax) * 20 : 0;
            return [
                'eleve' => $eleve,
                'moyenne' => $moyenne,
                'total_points' => $totalPoints,
                'total_points_max' => $totalPointsMax
            ];
        })->filter();

        // Trier par moyenne décroissante et calculer les rangs
        $elevesAvecMoyennes = $elevesAvecMoyennes
            ->sortByDesc('moyenne')
            ->values()
            ->map(function($item, $index) {
                $item['rang'] = $index + 1;
                return $item;
            });

        // Configuration pour améliorer les performances PDF
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $pdf = \PDF::loadView('registre.pdf', [
            'classe' => $classe,
            'session' => $session,
            'evaluation_id' => $evaluation,
            'eleves' => $elevesAvecMoyennes,
            'notes' => collect($notes),
            'ordreCompetences' => $ordreCompetences,
            'pointsMaxMap' => $pointsMaxMap,
            'sousCompetenceIds' => $sousCompetenceIds,
            'modaliteIds' => $modaliteIds
        ]);

        return $pdf->download('registre_classe_'.$classe_id.'_evaluation_'.$evaluation.'.pdf');
    }

    // Export Excel du registre (xlsx professionnel)
    public function exportExcel($session_id, $classe_id, $evaluation = null)
    {
        if ($evaluation === null) {
            $evaluation = request()->get('evaluation', 1);
        }
        
        // Trouver l'ID réel de l'évaluation
        $evaluationModel = \App\Models\Evaluation::where('classe_id', $classe_id)
            ->where('session_id', $session_id)
            ->where('numero_eval', $evaluation)
            ->first();
            
        if (!$evaluationModel) {
            return back()->with('error', 'Évaluation non trouvée');
        }
        
        $evaluationId = $evaluationModel->id;
        
        // Optimisation : Charger toutes les données en une seule fois
        $session = \App\Models\Session::findOrFail($session_id);
        $classe = \App\Models\Classe::with([
            'eleves' => function ($query) {
                $query->orderBy('nom');
            },
            'niveau.competences.sousCompetences.modalites' => function ($query) {
                $query->orderBy('nom');
            }
        ])->findOrFail($classe_id);
        
        $eleves = $classe->eleves;
        $niveau = $classe->niveau;
        
        // Préparer les structures de données
        $ordreCompetences = [];
        $pointsMaxMap = [];
        $sousCompetenceIds = [];
        $modaliteIds = [];
        
        foreach ($niveau->competences as $competence) {
            $compData = ['label' => $competence->nom, 'sous' => []];
            
            foreach ($competence->sousCompetences as $sc) {
                $modalitesDeSC = $sc->modalites;
                if ($modalitesDeSC->isNotEmpty()) {
                    $modalitesData = [];
                    $pointsData = [];
                    $sousCompetenceIds[$sc->nom] = $sc->id;
                    
                    foreach ($modalitesDeSC as $modalite) {
                        $modalitesData[] = $modalite->nom;
                        $modaliteIds[$modalite->nom] = $modalite->id;
                        $pointsMax = $modalite->pivot->points_max ?? 20;
                        $pointsData[] = $pointsMax;
                        $pointsMaxMap[$sc->nom][$modalite->nom] = $pointsMax;
                    }
                    
                    $compData['sous'][] = [
                        'label' => $sc->nom,
                        'modalites' => $modalitesData,
                        'points_max' => $pointsData
                    ];
                }
            }
            
            if (!empty($compData['sous'])) {
                $ordreCompetences[] = $compData;
            }
        }
        
        // Récupérer les notes avec une seule requête optimisée
        $notes = \App\Models\Note::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->where('evaluation_id', $evaluationId) // Utiliser l'ID réel de l'évaluation
            ->get()
            ->groupBy('eleve_id');
            
        // Préparer les notes pour chaque élève
        foreach ($eleves as $eleve) {
            $notesMapForEleve = [];
            if (isset($notes[$eleve->id])) {
                foreach ($notes[$eleve->id] as $note) {
                    if (!isset($notesMapForEleve[$note->sous_competence_id])) {
                        $notesMapForEleve[$note->sous_competence_id] = [];
                    }
                    $notesMapForEleve[$note->sous_competence_id][$note->modalite_id] = $note->valeur;
                }
            }
            $eleve->notes_map = $notesMapForEleve;
        }
        
        // Utilisation de Laravel Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RegistreExport(
                $session,
                $classe,
                $evaluation,
                $ordreCompetences,
                $eleves,
                $sousCompetenceIds,
                $modaliteIds,
                $pointsMaxMap
            ),
            'registre_classe_'.$classe_id.'_evaluation_'.$evaluation.'.xlsx'
        );
    }

    // Affichage du registre
    public function show($session_id, $classe_id, $evaluation = null)
    {
        try {
            // Forcer le nettoyage du cache pour garantir des données fraîches
            

            if ($evaluation === null) {
                $evaluation = request()->get('evaluation', 1);
            }
            
            // Trouver l'ID réel de l'évaluation
            $evaluationModel = \App\Models\Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->where('numero_eval', $evaluation)
                ->first();
                
            if (!$evaluationModel) {
                return back()->with('error', 'Aucune évaluation trouvée pour cette classe et session.');
            }
            
            $evaluationId = $evaluationModel->id;
            
            // Optimisation : Charger toutes les données en une seule fois
            $session = Session::findOrFail($session_id);
            $classe = Classe::with([
                'eleves' => function ($query) {
                    $query->orderBy('nom');
                },
                'niveau.competences.sousCompetences.modalites' => function ($query) {
                    $query->orderBy('nom');
                }
            ])->findOrFail($classe_id);

            $eleves = $classe->eleves;
            $niveau = $classe->niveau;

            if (!$niveau) {
                return back()->with('error', "Erreur critique : Aucun niveau n'est défini pour la classe '{$classe->nom}'. Veuillez l'attribuer dans la section d'administration.");
            }

            $ordreCompetences = [];
            $pointsMaxMap = [];
            $sousCompetenceIds = [];
            $modaliteIds = [];
            $allSousCompetences = collect();

            $baremes = \App\Models\SousCompetenceModalite::all()
                ->keyBy(function($item) {
                    return $item->sous_competence_id . '-' . $item->modalite_id;
                })
                ->map(function($item) {
                    return $item->points_max;
                });

            $grilles = [
                20 => [
                    ['min' => 0,  'max' => 9,  'cat' => 'nonacquis'], // C
                    ['min' => 10, 'max' => 14, 'cat' => 'encours'],   // B
                    ['min' => 15, 'max' => 17, 'cat' => 'acquis'],    // A
                    ['min' => 18, 'max' => 20, 'cat' => 'experts'],   // A+
                ],
                30 => [
                    ['min' => 0,  'max' => 14, 'cat' => 'nonacquis'], // C
                    ['min' => 15, 'max' => 20, 'cat' => 'encours'],   // B
                    ['min' => 21, 'max' => 26, 'cat' => 'acquis'],    // A
                    ['min' => 27, 'max' => 30, 'cat' => 'experts'],   // A+
                ],
                40 => [
                    ['min' => 0,  'max' => 19, 'cat' => 'nonacquis'], // C
                    ['min' => 20, 'max' => 29, 'cat' => 'encours'],   // B
                    ['min' => 30, 'max' => 35, 'cat' => 'acquis'],    // A
                    ['min' => 36, 'max' => 40, 'cat' => 'experts'],   // A+
                ],
            ];

            foreach ($niveau->competences as $competence) {
                $compData = ['label' => $competence->nom, 'sous' => []];

                foreach ($competence->sousCompetences as $sc) {
                    $allSousCompetences->push($sc);
                    $modalitesDeSC = $sc->modalites;

                    if ($modalitesDeSC->isNotEmpty()) {
                        $modalitesData = [];
                        $pointsData = [];
                        $sousCompetenceIds[$sc->nom] = $sc->id;

                        foreach ($modalitesDeSC as $modalite) {
                            $modalitesData[] = $modalite->nom;
                            $modaliteIds[$modalite->nom] = $modalite->id;
                            
                            $key = $sc->id . '-' . $modalite->id;
                            $pointsMax = $baremes->get($key, 20);

                            $pointsData[] = $pointsMax;
                            $pointsMaxMap[$sc->nom][$modalite->nom] = $pointsMax;
                        }

                        $compData['sous'][] = [
                            'label' => $sc->nom,
                            'modalites' => $modalitesData,
                            'points_max' => $pointsData
                        ];
                    }
                }

                if (!empty($compData['sous'])) {
                    $ordreCompetences[] = $compData;
                }
            }

            $notes = Note::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->where('evaluation_id', $evaluationId)
                ->get()
                ->groupBy('eleve_id');

            foreach ($eleves as $eleve) {
                $notesMapForEleve = [];
                if (isset($notes[$eleve->id])) {
                    foreach ($notes[$eleve->id] as $note) {
                        if (!isset($notesMapForEleve[$note->sous_competence_id])) {
                            $notesMapForEleve[$note->sous_competence_id] = [];
                        }
                        $notesMapForEleve[$note->sous_competence_id][$note->modalite_id] = $note->valeur;
                    }
                }
                $eleve->notes_map = $notesMapForEleve;
            }

            return view('registre.show', compact(
                'session',
                'classe',
                'evaluation',
                'evaluationId',
                'eleves',
                'ordreCompetences',
                'sousCompetenceIds',
                'modaliteIds',
                'pointsMaxMap',
                'allSousCompetences',
                'grilles'
            ));

        } catch (\Throwable $th) {
            // Mesure de débogage radicale pour afficher l'erreur exacte.
            dd($th);
        }
    }
    // Enregistrement des notes et génération du registre
    public function generer(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'classe_id' => 'required|exists:classes,id',
            'evaluation' => 'required|integer|min:1|max:3',
            'notes' => 'required|array'
        ]);

        $session = Session::findOrFail($request->session_id);
        $classe = Classe::findOrFail($request->classe_id);
        $evaluation = $request->evaluation;

        $sousCompetenceIds = $this->getSousCompetenceIds();
        $modaliteIds = $this->getModaliteIds();

        foreach ($request->notes as $eleve_id => $sousCompetences) {
            foreach ($sousCompetences as $sous_competence_id => $modalites) {
                foreach ($modalites as $modalite_id => $valeur) {
                    if ($valeur === null || $valeur === '') continue;

                    // Vérifier si la sous-competence et la modalite existent dans la structure fixe
                    $scLabel = array_search($sous_competence_id, $sousCompetenceIds);
                    $modaliteNom = array_search($modalite_id, $modaliteIds);
                    
                    if ($scLabel && $modaliteNom) {
                        // Valider la note selon la structure fixe
                        if (!$this->validateNote($scLabel, $modaliteNom, $valeur)) {
                            $pointsMax = $this->getPointsMax($scLabel, $modaliteNom);
                            return back()->with('error', "La note pour $scLabel/$modaliteNom ne peut pas dépasser $pointsMax points.");
                        }

                        Note::updateOrCreate(
                            [
                                'eleve_id' => $eleve_id,
                                'sous_competence_id' => $sous_competence_id,
                                'modalite_id' => $modalite_id,
                                'session_id' => $session->id,
                                'classe_id' => $classe->id,
                                'evaluation_id' => $trimestre
                            ],
                            [
                                'valeur' => $valeur
                            ]
                        );
                    }
                }
            }
        }

        return back()->with('success', 'Les notes ont été mises à jour avec succès.');
    }

    // Modification des notes directement depuis le registre
    public function updateNotes(Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'required|array',
                'notes.*.eleve_id' => 'required|exists:eleves,id',
                'notes.*.sc_id' => 'required|exists:sous_competences,id',
                'notes.*.modalite_id' => 'required|exists:modalites,id',
                'notes.*.session_id' => 'required|exists:sessions,id',
                'notes.*.classe_id' => 'required|exists:classes,id',
                'notes.*.evaluation_id' => 'required|exists:evaluations,id',
                'notes.*.valeur' => 'nullable|numeric|min:0',
            ]);

            $updatedCount = 0;
            $errors = [];



            foreach ($validated['notes'] as $noteData) {
                $valeur = $noteData['valeur'];

                // Récupérer les points maximum pour cette sous-compétence/modalité
                $pivot = \App\Models\SousCompetenceModalite::where('sous_competence_id', $noteData['sc_id'])
                    ->where('modalite_id', $noteData['modalite_id'])
                    ->first();

                if (!$pivot) {
                    $errors[] = "Configuration manquante pour la sous-compétence/modalité";
                    continue;
                }

                // Valider la note
                if ($valeur !== null && $valeur !== '') {
                    if (!\App\Services\RegistreOptimizationService::validateNote($valeur, $pivot->points_max)) {
                        $errors[] = "La note {$valeur} dépasse le maximum de {$pivot->points_max} points";
                        continue;
                    }
                }

                if ($valeur === null || $valeur === '') {
                    // Supprimer la note si elle est vide
                    Note::where([
                        'eleve_id' => $noteData['eleve_id'],
                        'sous_competence_id' => $noteData['sc_id'],
                        'modalite_id' => $noteData['modalite_id'],
                        'session_id' => $noteData['session_id'],
                        'classe_id' => $noteData['classe_id'],
                        'evaluation_id' => $noteData['evaluation_id']
                     ])->delete();
                } else {
                    // Mettre à jour ou créer la note
                Note::updateOrCreate(
                    [
                        'eleve_id' => $noteData['eleve_id'],
                        'sous_competence_id' => $noteData['sc_id'],
                        'modalite_id' => $noteData['modalite_id'],
                        'session_id' => $noteData['session_id'],
                        'classe_id' => $noteData['classe_id'],
                        'evaluation_id' => $noteData['evaluation_id']
                    ],
                    ['valeur' => $valeur]
                );
            }

                $updatedCount++;
            }

            // Nettoyer le cache pour cette évaluation
            if ($updatedCount > 0) {
                $evaluation = \App\Models\Evaluation::find($noteData['evaluation_id']);
                if ($evaluation) {
                    \App\Services\RegistreOptimizationService::clearCache(
                        $noteData['session_id'],
                        $noteData['classe_id'],
                        $evaluation->numero_eval
                    );
                }
            }

            $response = ['message' => "{$updatedCount} note(s) mise(s) à jour avec succès."];
            
            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur de validation Ajax lors de la mise à jour des notes', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Données invalides.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la mise à jour des notes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
                        return response()->json(['message' => 'Une erreur est survenue lors de la sauvegarde.'], 500);
        }
    }

    /**
     * Retourne les IDs d'évaluation pour un numéro ou un trimestre
     */
    protected function getEvalIdsForTrimestre($classe_id, $session_id, $periode) {
        if (preg_match('/trimestre(\d)/i', $periode, $m)) {
            $start = ((int)$m[1] - 1) * 3 + 1;
            $nums = [$start, $start+1, $start+2];
        } elseif (is_numeric($periode)) {
            // Vérifier si ce numéro d'UA existe pour cette classe/session
            $eval = \App\Models\Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->where('numero_eval', $periode)
                ->first();
            if ($eval) {
                $nums = [$periode];
            } else {
                $nums = [1];
            }
        } elseif (is_array($periode)) {
            $nums = $periode;
        } else {
            $nums = [1];
        }
        return \App\Models\Evaluation::where('classe_id', $classe_id)
            ->where('session_id', $session_id)
            ->whereIn('numero_eval', $nums)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Synthèse des compétences par sous-compétence pour une évaluation donnée (AJAX)
     */
    public function syntheseCompetences($session_id, $classe_id, $evaluation_id)
    {
        try {
            $classe = \App\Models\Classe::with('eleves')->findOrFail($classe_id);
            $session = \App\Models\Session::findOrFail($session_id);
            $evaluation = \App\Models\Evaluation::findOrFail($evaluation_id);
            $eleves = $classe->eleves;
            $sousCompetences = collect();
            if ($classe->niveau && $classe->niveau->competences) {
                foreach ($classe->niveau->competences as $comp) {
                    foreach ($comp->sousCompetences as $sc) {
                        $sousCompetences->push($sc);
                    }
                }
            }
            $notes = \App\Models\Note::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->where('evaluation_id', $evaluation_id)
                ->get();

            $synthese = [];
            foreach ($sousCompetences as $sc) {
                $scNotes = $notes->where('sous_competence_id', $sc->id);
                $inscrits = $eleves->count();
                $present = $scNotes->pluck('eleve_id')->unique()->count();
                $experts = $acquis = $encours = $nonacquis = 0;
                $experts_g = $acquis_g = $encours_g = $nonacquis_g = 0;
                $experts_f = $acquis_f = $encours_f = $nonacquis_f = 0;
                foreach ($eleves as $eleve) {
                    $note = $scNotes->where('eleve_id', $eleve->id)->first();
                    $moy = $note ? floatval($note->valeur) : null;
                    if ($moy !== null) {
                        if ($moy >= 18) {
                            $experts++;
                            if ($eleve->sexe === 'M') $experts_g++; else $experts_f++;
                        } elseif ($moy >= 15) {
                            $acquis++;
                            if ($eleve->sexe === 'M') $acquis_g++; else $acquis_f++;
                        } elseif ($moy >= 10) {
                            $encours++;
                            if ($eleve->sexe === 'M') $encours_g++; else $encours_f++;
                        } else {
                            $nonacquis++;
                            if ($eleve->sexe === 'M') $nonacquis_g++; else $nonacquis_f++;
                        }
                    }
                }
                $synthese[] = [
                    'sous_competence' => $sc->nom ?? $sc->libelle ?? '',
                    'inscrits' => $inscrits,
                    'present' => $present,
                    'experts' => $experts,
                    'experts_g' => $experts_g,
                    'experts_f' => $experts_f,
                    'acquis' => $acquis,
                    'acquis_g' => $acquis_g,
                    'acquis_f' => $acquis_f,
                    'encours' => $encours,
                    'encours_g' => $encours_g,
                    'encours_f' => $encours_f,
                    'nonacquis' => $nonacquis,
                    'nonacquis_g' => $nonacquis_g,
                    'nonacquis_f' => $nonacquis_f,
                    'experts_pourc' => $present > 0 ? round($experts * 100 / $present, 1) : 0,
                    'acquis_pourc' => $present > 0 ? round($acquis * 100 / $present, 1) : 0,
                    'encours_pourc' => $present > 0 ? round($encours * 100 / $present, 1) : 0,
                    'nonacquis_pourc' => $present > 0 ? round($nonacquis * 100 / $present, 1) : 0,
                ];
            }
            return view('registre.synthese-competences', compact('synthese', 'classe', 'session', 'evaluation'));
        } catch (\Exception $e) {
            return response()->view('registre.synthese-competences-error', [
                'error' => $e->getMessage(),
                'session_id' => $session_id,
                'classe_id' => $classe_id,
                'evaluation_id' => $evaluation_id
            ], 500);
        }
    }

    /**
     * Affiche la synthèse des compétences dans une page dédiée
     */
    public function syntheseCompetencesPage($session_id, $classe_id, $evaluation_id)
    {
        $data = $this->getSyntheseCompetencesData($session_id, $classe_id, $evaluation_id);
        return view('registre.synthese-competences-page', $data);
    }

    /**
     * Exporte la synthèse des compétences en PDF
     */
    public function syntheseCompetencesPdf($session_id, $classe_id, $evaluation_id)
    {
        $data = $this->getSyntheseCompetencesData($session_id, $classe_id, $evaluation_id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registre.synthese-competences-pdf', $data)->setPaper('a4', 'landscape');
        $nom = 'synthese_competences_classe_'.$classe_id.'_eval_'.$evaluation_id.'.pdf';
        return $pdf->download($nom);
    }

    /**
     * Récupère les données de synthèse des compétences (factorisation)
     */
    protected function getSyntheseCompetencesData($session_id, $classe_id, $evaluation_id)
    {
        // Grille de catégorisation selon le total de points max
        $grilles = [
            20 => [
                ['min' => 0,  'max' => 9,  'cat' => 'nonacquis'],
                ['min' => 10, 'max' => 14, 'cat' => 'encours'],
                ['min' => 15, 'max' => 17, 'cat' => 'acquis'],
                ['min' => 18, 'max' => 20, 'cat' => 'experts'],
            ],
            30 => [
                ['min' => 0,  'max' => 14, 'cat' => 'nonacquis'],
                ['min' => 15, 'max' => 20, 'cat' => 'encours'],
                ['min' => 21, 'max' => 26, 'cat' => 'acquis'],
                ['min' => 27, 'max' => 30, 'cat' => 'experts'],
            ],
            40 => [
                ['min' => 0,  'max' => 19, 'cat' => 'nonacquis'],
                ['min' => 20, 'max' => 29, 'cat' => 'encours'],
                ['min' => 30, 'max' => 34, 'cat' => 'acquis'],
                ['min' => 35, 'max' => 40, 'cat' => 'experts'],
            ],
        ];
        // Charger les relations de manière robuste pour éviter les erreurs critiques
        $classe = Classe::with([
            'eleves' => function ($query) {
                $query->orderBy('nom');
            },
            'niveau.competences.sousCompetences.modalites' => function ($query) {
                $query->withPivot('points_max')->orderBy('nom');
            }
        ])->findOrFail($classe_id);
        $session = \App\Models\Session::findOrFail($session_id);
        $evaluation = \App\Models\Evaluation::findOrFail($evaluation_id);
        $eleves = $classe->eleves;
        $competences = $classe->niveau && $classe->niveau->competences ? $classe->niveau->competences : collect();
        $notes = \App\Models\Note::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->where('evaluation_id', $evaluation_id)
            ->get();
        $synthese = [];
        foreach ($competences as $comp) {
    foreach ($comp->sousCompetences as $sc) {
        $libelle = $sc->nom ?? $sc->libelle ?? '';
        $inscrits_g = $eleves->where('sexe', 'M')->count();
        $inscrits_f = $eleves->where('sexe', 'F')->count();
        $inscrits_t = $eleves->count();
        // Points max pour cette sous-compétence (somme des points max de chaque modalité)
        $points_max = isset($sc->modalites) ? $sc->modalites->sum(function($mod) {
            return $mod->pivot->points_max ?? 0;
        }) : 0;
        \Log::debug('[SYNTH COMP] Sous-compétence: '.$libelle.' | Points max: '.$points_max.' | Modalités: '.json_encode(isset($sc->modalites) ? $sc->modalites->pluck('pivot.points_max') : []));
        $grille = $grilles[$points_max] ?? $grilles[20];
        $present_g = $present_f = 0;
        $experts_g = $experts_f = $acquis_g = $acquis_f = $encours_g = $encours_f = $nonacquis_g = $nonacquis_f = 0;
        foreach ($eleves as $eleve) {
            $notesEleve = $notes->where('eleve_id', $eleve->id)->where('sous_competence_id', $sc->id);
            $total_obtenu = $notesEleve->sum('valeur');
            $score_max_possible = 0;
            $note_couples = $notesEleve->map(function($n) { return [$n->sous_competence_id, $n->modalite_id]; })->unique();
            foreach ($note_couples as $pair) {
                [$scid, $modid] = $pair;
                $score_max_possible += \DB::table('sous_competence_modalites')
                    ->where('sous_competence_id', $scid)
                    ->where('modalite_id', $modid)
                    ->value('points_max');
            }
            $score_normalise = $score_max_possible > 0 ? round($total_obtenu * 40 / $score_max_possible) : 0;
            $cat_key = 'nonacquis';
            if (isset($grilles[40])) {
                foreach ($grilles[40] as $grille_item) {
                    if ($score_normalise >= $grille_item['min'] && $score_normalise <= $grille_item['max']) {
                        $cat_key = $grille_item['cat'];
                        break;
                    }
                }
            }
            if ($notesEleve->count() > 0) {
                if ($eleve->sexe === 'M') $present_g++;
                else $present_f++;
                if ($cat_key === 'experts') {
                    if ($eleve->sexe === 'M') $experts_g++; else $experts_f++;
                } elseif ($cat_key === 'acquis') {
                    if ($eleve->sexe === 'M') $acquis_g++; else $acquis_f++;
                } elseif ($cat_key === 'encours') {
                    if ($eleve->sexe === 'M') $encours_g++; else $encours_f++;
                } else {
                    if ($eleve->sexe === 'M') $nonacquis_g++; else $nonacquis_f++;
                }
            }
        }
        $present_t = $present_g + $present_f;
        $experts_t = $experts_g + $experts_f;
        $acquis_t = $acquis_g + $acquis_f;
        $encours_t = $encours_g + $encours_f;
        $nonacquis_t = $nonacquis_g + $nonacquis_f;
        $experts_g_p = $present_g > 0 ? round($experts_g * 100 / $present_g, 2) : 0;
        $experts_f_p = $present_f > 0 ? round($experts_f * 100 / $present_f, 2) : 0;
        $experts_t_p = $present_t > 0 ? round($experts_t * 100 / $present_t, 2) : 0;
        $acquis_g_p = $present_g > 0 ? round($acquis_g * 100 / $present_g, 2) : 0;
        $acquis_f_p = $present_f > 0 ? round($acquis_f * 100 / $present_f, 2) : 0;
        $acquis_t_p = $present_t > 0 ? round($acquis_t * 100 / $present_t, 2) : 0;
        $encours_g_p = $present_g > 0 ? round($encours_g * 100 / $present_g, 2) : 0;
        $encours_f_p = $present_f > 0 ? round($encours_f * 100 / $present_f, 2) : 0;
        $encours_t_p = $present_t > 0 ? round($encours_t * 100 / $present_t, 2) : 0;
        $nonacquis_g_p = $present_g > 0 ? round($nonacquis_g * 100 / $present_g, 2) : 0;
        $nonacquis_f_p = $present_f > 0 ? round($nonacquis_f * 100 / $present_f, 2) : 0;
        $nonacquis_t_p = $present_t > 0 ? round($nonacquis_t * 100 / $present_t, 2) : 0;
        \Log::debug('[SYNTH POURC] SC: '.$libelle.' | Présents G:'.$present_g.' F:'.$present_f.' T:'.$present_t.' | Experts:'.$experts_t.' ('.$experts_t_p.'%) | Acquis:'.$acquis_t.' ('.$acquis_t_p.'%) | Encours:'.$encours_t.' ('.$encours_t_p.'%) | Non acquis:'.$nonacquis_t.' ('.$nonacquis_t_p.'%)');
        $synthese[] = [
            'sous_competence' => $libelle,
            'inscrits_g' => $inscrits_g,
            'inscrits_f' => $inscrits_f,
            'inscrits_t' => $inscrits_t,
            'present_g' => $present_g,
            'present_f' => $present_f,
            'present_t' => $present_t,
            'experts_g' => $experts_g,
            'experts_f' => $experts_f,
            'experts_t' => $experts_t,
            'experts_g_p' => $experts_g_p,
            'experts_f_p' => $experts_f_p,
            'experts_t_p' => $experts_t_p,
            'acquis_g' => $acquis_g,
            'acquis_f' => $acquis_f,
            'acquis_t' => $acquis_t,
            'acquis_g_p' => $acquis_g_p,
            'acquis_f_p' => $acquis_f_p,
            'acquis_t_p' => $acquis_t_p,
            'encours_g' => $encours_g,
            'encours_f' => $encours_f,
            'encours_t' => $encours_t,
            'encours_g_p' => $encours_g_p,
            'encours_f_p' => $encours_f_p,
            'encours_t_p' => $encours_t_p,
            'nonacquis_g' => $nonacquis_g,
            'nonacquis_f' => $nonacquis_f,
            'nonacquis_t' => $nonacquis_t,
            'nonacquis_g_p' => $nonacquis_g_p,
            'nonacquis_f_p' => $nonacquis_f_p,
            'nonacquis_t_p' => $nonacquis_t_p,
        ];
    }
} // Fin boucle par sous-compétence

        // --- CALCUL TOTAL GLOBAL SANS DOUBLONS ---
        $totaux = [
            'inscrits_g' => $eleves->where('sexe', 'M')->count(),
            'inscrits_f' => $eleves->where('sexe', 'F')->count(),
            'inscrits_t' => $eleves->count(),
            'present_g' => 0, 'present_f' => 0, 'present_t' => 0,
            'experts_g' => 0, 'experts_f' => 0, 'experts_t' => 0,
            'acquis_g' => 0, 'acquis_f' => 0, 'acquis_t' => 0,
            'encours_g' => 0, 'encours_f' => 0, 'encours_t' => 0,
            'nonacquis_g' => 0, 'nonacquis_f' => 0, 'nonacquis_t' => 0,
            'experts_g_p' => 0, 'experts_f_p' => 0, 'experts_t_p' => 0,
            'acquis_g_p' => 0, 'acquis_f_p' => 0, 'acquis_t_p' => 0,
            'encours_g_p' => 0, 'encours_f_p' => 0, 'encours_t_p' => 0,
            'nonacquis_g_p' => 0, 'nonacquis_f_p' => 0, 'nonacquis_t_p' => 0,
        ];
        // Correction : récupération des sous-compétences réellement notées pour cette évaluation
        $scIdsForEval = $notes->where('evaluation_id', $evaluation_id)->pluck('sous_competence_id')->unique();
        \Log::debug('[SYNTH DEBUG] Nb eleves: '.count($eleves).' | Nb notes: '.count($notes).' | scIdsForEval: '.json_encode($scIdsForEval));
        $allSousCompetencesForEval = \App\Models\SousCompetence::whereIn('id', $scIdsForEval)
            ->with('modalites')
            ->get();
        // BARÈME GLOBAL FORCÉ POUR LA GRILLE OFFICIELLE (20, 30 ou 40)
        $total_points_max_evaluation = 40; // À adapter selon ta grille métier
        \Log::debug('[SYNTH TOTAL DEBUG] Barème global FORCÉ à '.$total_points_max_evaluation.' pour la grille officielle.');

        $totaux['bareme_global'] = $total_points_max_evaluation;
        $totaux['debug_eleves'] = [];
        foreach ($eleves as $eleve) {
            $notesEleveGlobal = $notes->where('eleve_id', $eleve->id)->whereIn('sous_competence_id', $scIdsForEval);
            $debug_txt = 'Élève '.$eleve->id.' '.$eleve->nom.' '.$eleve->prenom.' (sexe: '.$eleve->sexe.') | Notes: '.json_encode($notesEleveGlobal->pluck('valeur'));
            if ($notesEleveGlobal->isNotEmpty()) {
                if ($eleve->sexe === 'M') $totaux['present_g']++; else $totaux['present_f']++;
                $total_obtenu_global = $notesEleveGlobal->sum('valeur');
                // Calcul du max possible pour cet élève (somme des points max des couples notés)
                $score_max_possible = 0;
                $note_couples = $notesEleveGlobal->map(function($n) { return [$n->sous_competence_id, $n->modalite_id]; })->unique();
                foreach ($note_couples as $pair) {
                    [$scid, $modid] = $pair;
                    $score_max_possible += \DB::table('sous_competence_modalites')
                        ->where('sous_competence_id', $scid)
                        ->where('modalite_id', $modid)
                        ->value('points_max');
                }
                // Normalisation
                $score_normalise = $score_max_possible > 0 ? round($total_obtenu_global * 40 / $score_max_possible) : 0;
                $cat_key = 'nonacquis';
                if (isset($grilles[40])) {
                    foreach ($grilles[40] as $grille) {
                        if ($score_normalise >= $grille['min'] && $score_normalise <= $grille['max']) {
                            $cat_key = $grille['cat'];
                            break;
                        }
                    }
                }
                $debug_txt .= " | Total: $total_obtenu_global/$score_max_possible (normalisé: $score_normalise/40) | Catégorie: $cat_key";
                if ($eleve->sexe === 'M') $totaux[$cat_key.'_g']++; else $totaux[$cat_key.'_f']++;
            } else {
                $debug_txt .= ' | Aucune note';
            }
            $totaux['debug_eleves'][] = $debug_txt;
        }
        $totaux['present_t'] = $totaux['present_g'] + $totaux['present_f'];
        foreach (['experts', 'acquis', 'encours', 'nonacquis'] as $cat) {
            $totaux[$cat.'_t'] = $totaux[$cat.'_g'] + $totaux[$cat.'_f'];
            $totaux[$cat.'_g_p'] = $totaux['present_g'] > 0 ? round($totaux[$cat.'_g'] * 100 / $totaux['present_g'], 1) : 0;
            $totaux[$cat.'_f_p'] = $totaux['present_f'] > 0 ? round($totaux[$cat.'_f'] * 100 / $totaux['present_f'], 1) : 0;
            $totaux[$cat.'_t_p'] = $totaux['present_t'] > 0 ? round($totaux[$cat.'_t'] * 100 / $totaux['present_t'], 1) : 0;
        }
        return compact('synthese', 'classe', 'session', 'evaluation', 'totaux');
    }

    /**
     * Export Excel de la synthèse des compétences.
     */
    public function syntheseCompetencesExcel($session_id, $classe_id, $evaluation_id)
    {
        $data = $this->getSyntheseCompetencesData($session_id, $classe_id, $evaluation_id);
        $export = new \App\Exports\SyntheseCompetencesExport(
            $data['classe'], $data['session'], $data['evaluation'], $data['synthese'], $data['totaux'] ?? null
        );
        $filename = 'synthese_competences_classe_'.$classe_id.'_eval_'.$evaluation_id.'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }
}