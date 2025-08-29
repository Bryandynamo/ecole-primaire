@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le bulletin</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('bulletins.update', $bulletin->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="eleve_id" class="form-label">Élève</label>
            <select name="eleve_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($eleves as $eleve)
                    <option value="{{ $eleve->id }}" {{ old('eleve_id', $bulletin->eleve_id) == $eleve->id ? 'selected' : '' }}>{{ $eleve->nom }} {{ $eleve->prenom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="classe_id" class="form-label">Classe</label>
            <select name="classe_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" {{ old('classe_id', $bulletin->classe_id) == $classe->id ? 'selected' : '' }}>{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="session_id" class="form-label">Session</label>
            <select name="session_id" class="form-control" required>
                <option value="">Sélectionner...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ old('session_id', $bulletin->session_id) == $session->id ? 'selected' : '' }}>{{ $session->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="trimestre" class="form-label">Trimestre</label>
            <input type="text" name="trimestre" class="form-control" value="{{ old('trimestre', $bulletin->trimestre) }}" required>
        </div>
        <div class="mb-3">
    <label for="moyenne" class="form-label">Moyenne</label>
    <input type="number" step="0.01" name="moyenne" class="form-control" value="{{ $bulletin->moyenne }}" readonly>
    <div class="form-text">La moyenne est calculée automatiquement à partir des notes enregistrées.</div>
</div>
<div class="mb-3">
    <label for="decision" class="form-label">Décision</label>
    <input type="text" name="decision" class="form-control" value="{{ $bulletin->decision }}" readonly>
    <div class="form-text">La décision est générée automatiquement selon le règlement de l'école.</div>
</div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('bulletins.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
