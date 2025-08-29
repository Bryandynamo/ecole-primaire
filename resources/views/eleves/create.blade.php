@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un élève</h1>
    <form action="{{ route('eleves.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" value="{{ old('prenom') }}">
        </div>
        <div class="mb-3">
            <label for="sexe" class="form-label">Sexe</label>
            <select name="sexe" id="sexe" class="form-control" required>
                <option value="" disabled selected>Choisir...</option>
                <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" id="date_naissance" class="form-control" value="{{ old('date_naissance') }}">
        </div>
        <div class="mb-3">
            <label for="classe_id" class="form-label">Classe</label>
            <select name="classe_id" id="classe_id" class="form-control" required>
                <option value="" disabled selected>Choisir une classe...</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="session_id" class="form-label">Session</label>
            <select name="session_id" id="session_id" class="form-control" required>
                <option value="" disabled selected>Choisir une session...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>{{ $session->nom }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="{{ route('eleves.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>

@endsection
