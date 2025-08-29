@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la session académique</h1>
    <form action="{{ route('sessions.update', $session) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $session->nom }}" required>
        </div>
        <div class="mb-3">
            <label for="date_debut" class="form-label">Date de début</label>
            <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ $session->date_debut }}">
        </div>
        <div class="mb-3">
            <label for="date_fin" class="form-label">Date de fin</label>
            <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ $session->date_fin }}">
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('sessions.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
