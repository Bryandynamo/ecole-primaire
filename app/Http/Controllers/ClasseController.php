<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClasseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = \App\Models\Classe::with(['niveau', 'session'])->get();
        return view('classes.index', compact('classes'));
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
        return view('classes.create', compact('niveaux', 'sessions'));
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
            'niveau_id' => 'required|exists:niveaux,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        \App\Models\Classe::create($validated);
        return redirect()->route('classes.index')->with('success', 'Classe créée avec succès.');
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
        $classe = \App\Models\Classe::findOrFail($id);
        $niveaux = \App\Models\Niveau::all();
        $sessions = \App\Models\Session::all();
        return view('classes.edit', compact('classe', 'niveaux', 'sessions'));
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
        $classe = \App\Models\Classe::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'niveau_id' => 'required|exists:niveaux,id',
            'session_id' => 'required|exists:sessions,id',
        ]);
        $classe->update($validated);
        return redirect()->route('classes.index')->with('success', 'Classe modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $classe = \App\Models\Classe::findOrFail($id);
        $classe->delete();
        return redirect()->route('classes.index')->with('success', 'Classe supprimée avec succès.');
    }
}
