<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Niveau;

class NiveauController extends Controller
{
    public function index()
    {
        $niveaux = Niveau::all();
        return view('niveaux.index', compact('niveaux'));
    }

    public function create()
    {
        return view('niveaux.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
        ]);
        Niveau::create($validated);
        return redirect()->route('niveaux.index')->with('success', 'Niveau créé avec succès.');
    }

    public function edit($id)
    {
        $niveau = Niveau::findOrFail($id);
        return view('niveaux.edit', compact('niveau'));
    }

    public function update(Request $request, $id)
    {
        $niveau = Niveau::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
        ]);
        $niveau->update($validated);
        return redirect()->route('niveaux.index')->with('success', 'Niveau modifié avec succès.');
    }

    public function destroy($id)
    {
        $niveau = Niveau::findOrFail($id);
        $niveau->delete();
        return redirect()->route('niveaux.index')->with('success', 'Niveau supprimé avec succès.');
    }
}
