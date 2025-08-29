@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter une sous-compétence</h1>
    <form action="{{ route('sous-competences.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="competence_id" class="form-label">Compétence</label>
            <select name="competence_id" id="competence_id" class="form-control" required>
                <option value="" disabled selected>Choisir une compétence...</option>
                @foreach($competences as $competence)
                    <option value="{{ $competence->id }}" {{ old('competence_id') == $competence->id ? 'selected' : '' }}>{{ $competence->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="points_max" class="form-label">Points max</label>
            <input type="number" name="points_max" id="points_max" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="{{ route('sous-competences.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
