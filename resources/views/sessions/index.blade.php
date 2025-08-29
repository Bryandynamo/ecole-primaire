@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sessions académiques</h1>
    <a href="{{ route('sessions.create') }}" class="btn btn-primary w-100 mb-2">Nouvelle session</a>
    <div class="table-responsive">
<table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sessions as $session)
            <tr>
                <td>{{ $session->id }}</td>
                <td>{{ $session->nom }}</td>
                <td>{{ $session->date_debut }}</td>
                <td>{{ $session->date_fin }}</td>
                <td>
                    <a href="{{ route('sessions.edit', $session) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('sessions.destroy', $session) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette session ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
