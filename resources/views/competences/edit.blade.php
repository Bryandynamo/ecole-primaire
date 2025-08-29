@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la compétence</h1>
    <form action="{{ route('competences.update', $competence) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $competence->nom }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ $competence->description }}">
        </div>
        <div class="mb-3">
            <label for="niveau_id" class="form-label">Niveau</label>
            <select name="niveau_id" id="niveau_id" class="form-control" required>
                <option value="" disabled>Choisir un niveau...</option>
                @foreach($niveaux as $niveau)
                    <option value="{{ $niveau->id }}" {{ old('niveau_id', $competence->niveau_id) == $niveau->id ? 'selected' : '' }}>{{ $niveau->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="points_max" class="form-label">Points max</label>
            <input type="number" name="points_max" id="points_max" class="form-control" value="{{ $competence->points_max }}" required>
        </div>
        <div class="mb-3">
            <label for="session_id" class="form-label">Session</label>
            <select name="session_id" id="session_id" class="form-control" required>
                <option value="" disabled>Choisir une session...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ old('session_id', $competence->session_id) == $session->id ? 'selected' : '' }}>{{ $session->nom }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('competences.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
