@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
    <h1>Classes</h1>
    <a href="{{ route('classes.create') }}" class="btn btn-primary w-100 mb-2">Nouvelle classe</a>
    <div class="table-responsive">
<table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Niveau</th>
                <th>Session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($classes as $classe)
            <tr>
                <td>{{ $classe->id }}</td>
                <td>{{ $classe->nom }}</td>
                <td>{{ $classe->niveau_id }}</td>
                <td>{{ $classe->session_id }}</td>
                <td>
                    <a href="{{ route('classes.edit', $classe) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('classes.destroy', $classe) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette classe ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
