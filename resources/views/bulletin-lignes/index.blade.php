@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lignes de bulletin</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('bulletin-lignes.create') }}" class="btn btn-primary w-100 mb-2">Nouvelle ligne</a>
    <div class="table-responsive">
<table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Bulletin</th>
                <th>Sous-compétence</th>
                <th>Modalité</th>
                <th>Note</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $ligne)
                <tr>
                    <td>{{ $ligne->id }}</td>
                    <td>{{ $ligne->bulletin->id ?? '' }}</td>
                    <td>{{ $ligne->sousCompetence->nom ?? '' }}</td>
                    <td>{{ $ligne->modalite->nom ?? '' }}</td>
                    <td>{{ $ligne->note }}</td>
                    <td>
                        <a href="{{ route('bulletin-lignes.edit', $ligne->id) }}" class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('bulletin-lignes.destroy', $ligne->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette ligne ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
