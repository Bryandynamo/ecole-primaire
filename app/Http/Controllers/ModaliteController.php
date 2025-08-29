<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModaliteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modalites = \App\Models\Modalite::all();
        return view('modalites.index', compact('modalites'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('modalites.create');
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
        ]);
        \App\Models\Modalite::create($validated);
        return redirect()->route('modalites.index')->with('success', 'Modalité créée avec succès.');
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
        $modalite = \App\Models\Modalite::findOrFail($id);
        return view('modalites.edit', compact('modalite'));
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
        $modalite = \App\Models\Modalite::findOrFail($id);
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
        ]);
        $modalite->update($validated);
        return redirect()->route('modalites.index')->with('success', 'Modalité modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modalite = \App\Models\Modalite::findOrFail($id);
        $modalite->delete();
        return redirect()->route('modalites.index')->with('success', 'Modalité supprimée avec succès.');
    }
}
