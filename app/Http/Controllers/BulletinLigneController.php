<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulletinLigne;
use App\Models\Bulletin;
use App\Models\SousCompetence;
use App\Models\Modalite;

class BulletinLigneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lignes = BulletinLigne::with(['bulletin', 'sousCompetence', 'modalite'])->get();
        return view('bulletin-lignes.index', compact('lignes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bulletins = Bulletin::all();
        $sous_competences = SousCompetence::all();
        $modalites = Modalite::all();
        return view('bulletin-lignes.create', compact('bulletins', 'sous_competences', 'modalites'));
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
            'bulletin_id' => 'required|exists:bulletins,id',
            'sous_competence_id' => 'required|exists:sous_competences,id',
            'modalite_id' => 'required|exists:modalites,id',
            'note' => 'required|numeric|min:0',
        ]);
        BulletinLigne::create($validated);
        return redirect()->route('bulletin-lignes.index')->with('success', 'Ligne de bulletin créée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ligne = BulletinLigne::findOrFail($id);
        $bulletins = Bulletin::all();
        $sous_competences = SousCompetence::all();
        $modalites = Modalite::all();
        return view('bulletin-lignes.edit', compact('ligne', 'bulletins', 'sous_competences', 'modalites'));
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
        $ligne = BulletinLigne::findOrFail($id);
        $validated = $request->validate([
            'bulletin_id' => 'required|exists:bulletins,id',
            'sous_competence_id' => 'required|exists:sous_competences,id',
            'modalite_id' => 'required|exists:modalites,id',
            'note' => 'required|numeric|min:0',
        ]);
        $ligne->update($validated);
        return redirect()->route('bulletin-lignes.index')->with('success', 'Ligne de bulletin modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ligne = BulletinLigne::findOrFail($id);
        $ligne->delete();
        return redirect()->route('bulletin-lignes.index')->with('success', 'Ligne de bulletin supprimée avec succès.');
    }
}
