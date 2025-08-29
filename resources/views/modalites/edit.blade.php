@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la modalité</h1>
    <form action="{{ route('modalites.update', $modalite) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="id" class="form-label">ID de la modalité</label>
            <input type="text" name="id" id="id" class="form-control" value="{{ $modalite->id }}" readonly>
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $modalite->nom }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ $modalite->description }}">
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('modalites.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
