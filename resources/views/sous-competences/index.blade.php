@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sous-compétences</h1>
    <a href="{{ route('sous-competences.create') }}" class="btn btn-primary mb-3">Nouvelle sous-compétence</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Compétence</th>
                <th>Nom</th>
                <th>Points max</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sous_competences as $sous_competence)
            <tr>
                <td>{{ $sous_competence->id }}</td>
                <td>{{ $sous_competence->competence_id }}</td>
                <td>{{ $sous_competence->nom }}</td>
                <td>{{ $sous_competence->points_max }}</td>
                <td>
                    <a href="{{ route('sous-competences.edit', $sous_competence) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('sous-competences.destroy', $sous_competence) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette sous-compétence ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
