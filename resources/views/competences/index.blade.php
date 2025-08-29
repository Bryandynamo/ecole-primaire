@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
    <h1>Compétences</h1>
    <a href="{{ route('competences.create') }}" class="btn btn-primary w-100 mb-2">Nouvelle compétence</a>
    <div class="table-responsive">
<table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Niveau</th>
                <th>Points max</th>
                <th>Session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($competences as $competence)
            <tr>
                <td>{{ $competence->id }}</td>
                <td>{{ $competence->nom }}</td>
                <td>{{ $competence->description }}</td>
                <td>{{ $competence->niveau_id }}</td>
                <td>{{ $competence->points_max }}</td>
                <td>{{ $competence->session_id }}</td>
                <td>
                    <a href="{{ route('competences.edit', $competence) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('competences.destroy', $competence) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette compétence ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
