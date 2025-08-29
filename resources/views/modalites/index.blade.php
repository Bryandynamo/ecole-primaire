@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
    <h1>Modalités</h1>
    <a href="{{ route('modalites.create') }}" class="btn btn-primary w-100 mb-2">Nouvelle modalité</a>
    <div class="table-responsive">
<table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($modalites as $modalite)
            <tr>
                <td>{{ $modalite->id }}</td>
                <td>{{ $modalite->nom }}</td>
                <td>{{ $modalite->description }}</td>
                <td>
                    <a href="{{ route('modalites.edit', $modalite) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('modalites.destroy', $modalite) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette modalité ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
