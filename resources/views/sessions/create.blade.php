@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer une session académique</h1>
    <form action="{{ route('sessions.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date_debut" class="form-label">Date de début</label>
            <input type="date" name="date_debut" id="date_debut" class="form-control">
        </div>
        <div class="mb-3">
            <label for="date_fin" class="form-label">Date de fin</label>
            <input type="date" name="date_fin" id="date_fin" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Créer</button>
        <a href="{{ route('sessions.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
