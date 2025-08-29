@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Enseignants</h1>
    <a href="{{ route('enseignants.create') }}" class="btn btn-primary w-100 mb-2">Nouvel enseignant</a>
    <div class="table-responsive">
<table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Matricule</th>
                <th>Classe</th>
                <th>Session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($enseignants as $enseignant)
            <tr>
                <td>{{ $enseignant->id }}</td>
                <td>{{ $enseignant->nom }}</td>
                <td>{{ $enseignant->prenom }}</td>
                <td>{{ $enseignant->matricule }}</td>
                <td>{{ $enseignant->classe_id }}</td>
                <td>{{ $enseignant->session_id }}</td>
                <td>
                    <a href="{{ route('enseignants.edit', $enseignant) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('enseignants.destroy', $enseignant) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet enseignant ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
