@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier l'enseignant</h1>
    <form action="{{ route('enseignants.update', $enseignant) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $enseignant->nom }}" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" value="{{ $enseignant->prenom }}">
        </div>
        
        <div class="mb-3">
            <label for="classe_id" class="form-label">Classe</label>
            <select name="classe_id" id="classe_id" class="form-control" required>
                <option value="" disabled>Choisir une classe...</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" {{ old('classe_id', $enseignant->classe_id) == $classe->id ? 'selected' : '' }}>{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="session_id" class="form-label">Session</label>
            <select name="session_id" id="session_id" class="form-control" required>
                <option value="" disabled>Choisir une session...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ old('session_id', $enseignant->session_id) == $session->id ? 'selected' : '' }}>{{ $session->nom }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('enseignants.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
