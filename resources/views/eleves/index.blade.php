@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-6 fw-bold text-primary">Liste des élèves</h1>
        <a href="{{ route('eleves.create') }}" class="btn btn-success btn-lg"><i class="bi bi-person-plus"></i> Nouvel élève</a>
    </div>
    <div class="alert alert-info mb-4">
        Gérer les élèves inscrits dans l'établissement. Retrouvez leur matricule, classe, session et informations principales.<br>
        <strong>Total :</strong> {{ $eleves->count() }} élèves.
    </div>
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0 table-responsive">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Matricule</th>
                    <th>Sexe</th>
                    <th>Date de naissance</th>
                    <th>Classe</th>
                    <th>Session</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($eleves as $eleve)
                <tr>
                    <td><span class="badge bg-secondary">{{ $eleve->id }}</span></td>
                    <td><strong>{{ $eleve->nom }}</strong></td>
                    <td>{{ $eleve->prenom }}</td>
                    <td><span class="badge bg-info">{{ $eleve->matricule }}</span></td>
                    <td>
                        @if(strtolower($eleve->sexe) == 'm')
                            <span class="btn btn-primary w-100 mb-2">M</span>
                        @elseif(strtolower($eleve->sexe) == 'f')
                            <span class="badge bg-pink">F</span>
                        @else
                            <span class="badge bg-secondary">{{ $eleve->sexe }}</span>
                        @endif
                    </td>
                    <td>{{ $eleve->date_naissance }}</td>
                    <td><span class="badge bg-light text-dark">{{ $eleve->classe_id }}</span></td>
                    <td><span class="badge bg-light text-dark">{{ $eleve->session_id }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('eleves.edit', $eleve) }}" class="btn btn-sm btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('eleves.destroy', $eleve) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer cet élève ?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
</div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

@endsection
