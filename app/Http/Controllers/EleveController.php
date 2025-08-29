<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EleveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $eleves = \App\Models\Eleve::with(['classe', 'session'])->get();
        return view('eleves.index', compact('eleves'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $classes = \App\Models\Classe::all();
        $sessions = \App\Models\Session::all();
        return view('eleves.create', compact('classes', 'sessions'));
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
            'id' => 'required|string|max:20|unique:eleves,id',
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'matricule' => 'required|string|max:30|unique:eleves,matricule',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        \App\Models\Eleve::create($validated);
        return redirect()->route('eleves.index')->with('success', 'Élève ajouté avec succès.');
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
        $eleve = \App\Models\Eleve::findOrFail($id);
        $classes = \App\Models\Classe::all();
        $sessions = \App\Models\Session::all();
        return view('eleves.edit', compact('eleve', 'classes', 'sessions'));
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
        $eleve = \App\Models\Eleve::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'matricule' => 'required|string|max:30|unique:eleves,matricule,' . $eleve->id . ',id',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        $eleve->update($validated);
        return redirect()->route('eleves.index')->with('success', 'Élève modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $eleve = \App\Models\Eleve::findOrFail($id);
        $eleve->delete();
        return redirect()->route('eleves.index')->with('success', 'Élève supprimé avec succès.');
    }
}
