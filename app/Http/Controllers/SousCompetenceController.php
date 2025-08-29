<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SousCompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sous_competences = \App\Models\SousCompetence::with('competence')->get();
        return view('sous-competences.index', compact('sous_competences'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $competences = \App\Models\Competence::all();
        return view('sous-competences.create', compact('competences'));
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
            'competence_id' => 'required|exists:competences,id',
            'nom' => 'required|string|max:50',
            'points_max' => 'required|integer|min:1',
        ]);
        \App\Models\SousCompetence::create($validated);
        return redirect()->route('sous-competences.index')->with('success', 'Sous-compétence créée avec succès.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sous_competence = \App\Models\SousCompetence::findOrFail($id);
        $competences = \App\Models\Competence::all();
        return view('sous-competences.edit', compact('sous_competence', 'competences'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $sous_competence = \App\Models\SousCompetence::findOrFail($id);
        $validated = $request->validate([
            'competence_id' => 'required|exists:competences,id',
            'nom' => 'required|string|max:50',
            'points_max' => 'required|integer|min:1',
        ]);
        $sous_competence->update($validated);
        return redirect()->route('sous-competences.index')->with('success', 'Sous-compétence modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sous_competence = \App\Models\SousCompetence::findOrFail($id);
        $sous_competence->delete();
        return redirect()->route('sous-competences.index')->with('success', 'Sous-compétence supprimée avec succès.');
    }
}
