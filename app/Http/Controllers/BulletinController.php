<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bulletin;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BulletinSpreadsheetBuilder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use App\Services\BulletinOptimizationService;

class BulletinController extends Controller
{
    /**
     * Générer l'ensemble des bulletins d'un trimestre (3 évaluations)
     */
    public function trimestre($session_id, $classe_id)
    {
        $trimestre = request('trimestre', 1);
        return $this->generate($session_id, $classe_id, 'trimestre'.$trimestre);
    }

    /**
     * Générer les bulletins pour une évaluation spécifique
     */
    public function evaluation($session_id, $classe_id, $evaluation_num)
    {
        return $this->generate($session_id, $classe_id, $evaluation_num);
    }

    private function getEvaluationIds($session_id, $classe_id, array $numero_evals)
    {
        return \App\Models\Evaluation::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->whereIn('numero_eval', $numero_evals)
            ->pluck('id')
            ->toArray();
    }

    private function generate($session_id, $classe_id, $periode)
    {
        // Utiliser le service d'optimisation avec cache
        $bulletins = \App\Services\BulletinOptimizationService::buildOptimizedBulletins($session_id, $classe_id, $periode);
        
        try {
            // Correction de la structure des données pour éviter les tableaux vides/mal formés
            if (!is_array($bulletins)) {
                $bulletins = [];
            }
            foreach ($bulletins as &$b) {
                if (!isset($b['structure']) || !is_array($b['structure'])) $b['structure'] = [];
                if (!isset($b['notes']) || !is_array($b['notes'])) $b['notes'] = [];
                if (!isset($b['totauxSousComp']) || !is_array($b['totauxSousComp'])) $b['totauxSousComp'] = [];
                if (!isset($b['totalGeneral']) || !is_array($b['totalGeneral'])) $b['totalGeneral'] = [];
                if (!isset($b['profilClasse']) || !is_array($b['profilClasse'])) $b['profilClasse'] = [];
                if (!isset($b['rangsUA']) || !is_array($b['rangsUA'])) $b['rangsUA'] = [];
            }
            unset($b);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bulletins.modele_officiel', [
                'bulletins' => $bulletins,
                'trimestre' => is_numeric($periode) ? $periode : null
            ]);
            return $pdf->stream('bulletins_officiels.pdf');
        } catch (\Throwable $e) {
            return response('<pre>'.e($e)."\n".e($e->getTraceAsString()).'</pre>', 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bulletins = Bulletin::with(['eleve', 'classe', 'session'])->get();
        return view('bulletins.index', compact('bulletins'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $eleves = Eleve::all();
        $classes = Classe::all();
        $sessions = Session::all();
        return view('bulletins.create', compact('eleves', 'classes', 'sessions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
            'trimestre' => 'required|string|max:10',
            'moyenne' => 'required|numeric|min:0',
            'decision' => 'nullable|string|max:50',
        ]);
        Bulletin::create($validated);
        return redirect()->route('bulletins.index')->with('success', 'Bulletin créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $eleves = Eleve::all();
        $classes = Classe::all();
        $sessions = Session::all();
        return view('bulletins.edit', compact('bulletin', 'eleves', 'classes', 'sessions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $validated = $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
            'trimestre' => 'required|string|max:10',
            'moyenne' => 'required|numeric|min:0',
            'decision' => 'nullable|string|max:50',
        ]);
        $bulletin->update($validated);
        return redirect()->route('bulletins.index')->with('success', 'Bulletin modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->delete();
        return redirect()->route('bulletins.index')->with('success', 'Bulletin supprimé avec succès.');
    }

    /**
     * Exporter le bulletin au format PDF
     */
    // Ancienne méthode exportPdf conservée pour compatibilité
    public function exportPdf($id)
    {
        $bulletin = Bulletin::with(['eleve', 'classe', 'session'])->findOrFail($id);
        // Charger les compétences, sous-compétences, modalités et notes associées
        $competences = \App\Models\Competence::with('sousCompetences')->get();
        $modalites = \App\Models\Modalite::all();
        // Préparer les notes sous forme de tableau indexé [sous_competence_id][modalite_id][trimestre]
        $notes = [];
        $eleve = $bulletin->eleve;
        $allNotes = $eleve->notes()->get();
        foreach ($competences as $competence) {
            foreach ($competence->sousCompetences as $sousCompetence) {
                foreach ($modalites as $modalite) {
                    foreach ([1,2,3] as $trimestre) {
                        $note = $allNotes->where('sous_competence_id', $sousCompetence->id)
                                           ->where('modalite_id', $modalite->id)
                                           ->where('trimestre', $trimestre)->first();
                        $notes[$sousCompetence->id][$modalite->id]['T'.$trimestre]['note'] = $note->valeur ?? '';
                        $notes[$sousCompetence->id][$modalite->id]['T'.$trimestre]['cote'] = $note->cote ?? '';
                    }
                    // Total sur les 3 trimestres
                    $total = 0;
                    foreach ([1,2,3] as $trimestre) {
                        $val = $notes[$sousCompetence->id][$modalite->id]['T'.$trimestre]['note'];
                        if (is_numeric($val)) $total += $val;
                    }
                    $notes[$sousCompetence->id][$modalite->id]['total'] = $total;
                }
            }
        }
        $pdf = Pdf::loadView('bulletins.show', compact('bulletin', 'competences', 'modalites', 'notes'));
        return $pdf->download('bulletin_'.$bulletin->eleve->nom.'_'.$bulletin->eleve->prenom.'.pdf');
    }
}
