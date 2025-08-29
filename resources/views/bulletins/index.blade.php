@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-6 fw-bold text-primary">Bulletins scolaires</h1>
        <a href="{{ route('bulletins.create') }}" class="btn btn-success btn-lg"><i class="bi bi-plus-circle"></i> Nouveau bulletin</a>
    </div>
    <div class="alert alert-info mb-4">
        Retrouvez ici tous les bulletins générés pour chaque élève, avec accès rapide à l'impression PDF, la modification et la suppression.<br>
        <strong>Total :</strong> {{ $bulletins->count() }} bulletins.
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive shadow-sm rounded">
        <div class="table-responsive">
<table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Session</th>
                    <th>Trimestre</th>
                    <th>Moyenne</th>
                    <th>Décision</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bulletins as $bulletin)
                    <tr>
                        <td><span class="btn btn-secondary w-100 mb-2">{{ $bulletin->id }}</span></td>
                        <td><strong>{{ $bulletin->eleve->nom ?? '' }} {{ $bulletin->eleve->prenom ?? '' }}</strong></td>
                        <td>{{ $bulletin->classe->nom ?? '' }}</td>
                        <td>{{ $bulletin->session->nom ?? '' }}</td>
                        <td><span class="badge bg-info">{{ $bulletin->trimestre }}</span></td>
                        <td><span class="btn btn-primary w-100 mb-2">{{ $bulletin->moyenne }}</span></td>
                        <td>
                            @if(strtolower($bulletin->decision) == 'admis')
                                <span class="badge bg-success">Admis</span>
                            @elseif(strtolower($bulletin->decision) == 'redouble')
                                <span class="badge bg-danger">Redouble</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $bulletin->decision }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bulletins.show', $bulletin->id) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('bulletins.edit', $bulletin->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('bulletins.exportPdf', $bulletin->id) }}" class="btn btn-sm btn-outline-success" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <form action="{{ route('bulletins.destroy', $bulletin->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer ce bulletin ?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</div>
    </div>
</div>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

@endsection
