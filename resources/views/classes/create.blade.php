@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer une classe</h1>
    <form action="{{ route('classes.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="niveau_id" class="form-label">Niveau</label>
            <select name="niveau_id" id="niveau_id" class="form-control" required>
                <option value="" disabled selected>Choisir un niveau...</option>
                @foreach($niveaux as $niveau)
                    <option value="{{ $niveau->id }}" {{ old('niveau_id') == $niveau->id ? 'selected' : '' }}>{{ $niveau->nom }}</option>
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
        <button type="submit" class="btn btn-success">Créer</button>
        <a href="{{ route('classes.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
