@extends('layouts.app')

@section('content')
@php
// Fonction pour convertir un nombre en format ordinal français
function rangOrdinal($nombre) {
    if ($nombre == 1) return '1er';
    if ($nombre == 2) return '2ème';
    if ($nombre == 3) return '3ème';
    if ($nombre == 4) return '4ème';
    if ($nombre == 5) return '5ème';
    if ($nombre == 6) return '6ème';
    if ($nombre == 7) return '7ème';
    if ($nombre == 8) return '8ème';
    if ($nombre == 9) return '9ème';
    if ($nombre == 10) return '10ème';
    return $nombre . 'ème';
}
@endphp
<div class="container py-4" id="bulletin-pdf">
    <div class="card shadow-lg border-primary mb-4">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col text-center">
                    <div class="fw-bold text-uppercase text-secondary">REPUBLIQUE DU CAMEROUN</div>
                    <div class="text-secondary">PAIX - TRAVAIL - PATRIE</div>
                    <div class="mb-2">MINISTERE DE L'EDUCATION DE BASE <span class="mx-3"><img src="/logo.png" alt="Logo" height="48"></span></div>
                    <h2 class="bulletin-title text-primary">BULLETIN DE NOTES</h2>
                    <div class="subtitle mb-2">
                        Année scolaire : <b>{{ $bulletin->session->nom ?? '' }}</b> | Trimestre : <b>{{ $bulletin->trimestre }}</b>
                    </div>
                    <div class="subtitle mb-2">
                        Nom de l'élève : <b>{{ $bulletin->eleve->nom ?? '' }} {{ $bulletin->eleve->prenom ?? '' }}</b> | Classe : <b>{{ $bulletin->classe->nom ?? '' }}</b>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <div class="table-responsive">
<table class="table table-bordered table-sm text-center align-middle mb-4">
                    <thead class="table-primary">
                        <tr>
                            <th rowspan="2">COMPÉTENCES</th>
                            <th rowspan="2">SOUS COMPÉTENCES</th>
                            <th rowspan="2">MODALITÉ</th>
                            <th colspan="2">1er TRIMESTRE</th>
                            <th colspan="2">2ème TRIMESTRE</th>
                            <th colspan="2">3ème TRIMESTRE</th>
                            <th rowspan="2">TOTAL</th>
                        </tr>
                        <tr>
                            <th>Note</th>
                            <th>Cote</th>
                            <th>Note</th>
                            <th>Cote</th>
                            <th>Note</th>
                            <th>Cote</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competences as $competence)
                            @foreach($competence->sousCompetences as $sousCompetence)
                                @foreach($modalites as $modalite)
                                    <tr>
                                        @if($loop->first && $loop->parent->first && $loop->parent->parent->first)
                                            <td rowspan="{{ $competence->sousCompetences->count() * $modalites->count() }}" class="fw-bold bg-light">{{ $competence->nom }}</td>
                                        @endif
                                        @if($loop->first)
                                            <td rowspan="{{ $modalites->count() }}" class="bg-light">{{ $sousCompetence->nom }}</td>
                                        @endif
                                        <td>{{ $modalite->nom }}</td>
                                        <td>{{ $notes[$sousCompetence->id][$modalite->id]['T1']['note'] ?? '' }}</td>
                                        <td>{{ $notes[$sousCompetence->id][$modalite->id]['T1']['cote'] ?? '' }}</td>
                            <td>{{ $notes[$sousCompetence->id][$modalite->id]['T2']['note'] ?? '' }}</td>
                            <td>{{ $notes[$sousCompetence->id][$modalite->id]['T2']['cote'] ?? '' }}</td>
                            <td>{{ $notes[$sousCompetence->id][$modalite->id]['T3']['note'] ?? '' }}</td>
                            <td>{{ $notes[$sousCompetence->id][$modalite->id]['T3']['cote'] ?? '' }}</td>
                            <td>{{ $notes[$sousCompetence->id][$modalite->id]['total'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
    <div class="mt-3">
        <b>Moyenne générale :</b> {{ $bulletin->moyenne }} <span class="text-primary fw-bold">({{ rangOrdinal($bulletin->rang ?? 0) }})</span><br>
        <b>Décision :</b> {{ $bulletin->decision }}
    </div>
    <div class="mt-4">
        <a href="{{ route('bulletins.exportPdf', $bulletin->id) }}" class="btn btn-primary w-100 mb-2">Imprimer / Exporter PDF</a>
        <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
    </div>
</div>
@endsection
