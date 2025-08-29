<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $competences = \App\Models\Competence::with(['niveau', 'session'])->get();
        return view('competences.index', compact('competences'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $niveaux = \App\Models\Niveau::all();
        $sessions = \App\Models\Session::all();
        return view('competences.create', compact('niveaux', 'sessions'));
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
            'nom' => 'required|string|max:50',
            'description' => 'nullable|string',
            'niveau_id' => 'required|exists:niveaux,id',
            'points_max' => 'required|integer|min:1',
            'session_id' => 'required|exists:sessions,id',
        ]);
        \App\Models\Competence::create($validated);
        return redirect()->route('competences.index')->with('success', 'Compétence créée avec succès.');
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
        $competence = \App\Models\Competence::findOrFail($id);
        $niveaux = \App\Models\Niveau::all();
        $sessions = \App\Models\Session::all();
        return view('competences.edit', compact('competence', 'niveaux', 'sessions'));
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
        $competence = \App\Models\Competence::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'description' => 'nullable|string',
            'niveau_id' => 'required|exists:niveaux,id',
            'points_max' => 'required|integer|min:1',
            'session_id' => 'required|exists:sessions,id',
        ]);
        $competence->update($validated);
        return redirect()->route('competences.index')->with('success', 'Compétence modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $competence = \App\Models\Competence::findOrFail($id);
        $competence->delete();
        return redirect()->route('competences.index')->with('success', 'Compétence supprimée avec succès.');
    }
}
