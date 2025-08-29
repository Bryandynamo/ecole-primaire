<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnseignantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $enseignants = \App\Models\Enseignant::with(['classe', 'session'])->get();
        return view('enseignants.index', compact('enseignants'));
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
        return view('enseignants.create', compact('classes', 'sessions'));
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
            'prenom' => 'nullable|string|max:50',
            'matricule' => 'required|string|max:30|unique:enseignants,matricule',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        \App\Models\Enseignant::create($validated);
        return redirect()->route('enseignants.index')->with('success', 'Enseignant ajouté avec succès.');
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
        $enseignant = \App\Models\Enseignant::findOrFail($id);
        $classes = \App\Models\Classe::all();
        $sessions = \App\Models\Session::all();
        return view('enseignants.edit', compact('enseignant', 'classes', 'sessions'));
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
        $enseignant = \App\Models\Enseignant::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'matricule' => 'required|string|max:30|unique:enseignants,matricule,' . $enseignant->id . ',id',
            'classe_id' => 'required|exists:classes,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        $enseignant->update($validated);
        return redirect()->route('enseignants.index')->with('success', 'Enseignant modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $enseignant = \App\Models\Enseignant::findOrFail($id);
        $enseignant->delete();
        return redirect()->route('enseignants.index')->with('success', 'Enseignant supprimé avec succès.');
    }
}
