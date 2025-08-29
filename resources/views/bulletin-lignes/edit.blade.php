@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la ligne de bulletin</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('bulletin-lignes.update', $ligne->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="bulletin_id" class="form-label">Bulletin</label>
            <select name="bulletin_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($bulletins as $bulletin)
                    <option value="{{ $bulletin->id }}" {{ old('bulletin_id', $ligne->bulletin_id) == $bulletin->id ? 'selected' : '' }}>{{ $bulletin->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="sous_competence_id" class="form-label">Sous-compétence</label>
            <select name="sous_competence_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($sous_competences as $sc)
                    <option value="{{ $sc->id }}" {{ old('sous_competence_id', $ligne->sous_competence_id) == $sc->id ? 'selected' : '' }}>{{ $sc->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="modalite_id" class="form-label">Modalité</label>
            <select name="modalite_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($modalites as $modalite)
                    <option value="{{ $modalite->id }}" {{ old('modalite_id', $ligne->modalite_id) == $modalite->id ? 'selected' : '' }}>{{ $modalite->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <input type="number" step="0.01" name="note" class="form-control" value="{{ old('note', $ligne->note) }}" required>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('bulletin-lignes.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
