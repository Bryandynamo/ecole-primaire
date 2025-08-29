@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Répertoire des registres</h1>
    <form method="GET" action="{{ route('registre.repertoire') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="session_id" class="form-label">Session académique</label>
            <select name="session_id" id="session_id" class="form-select" required @if(isset($sessions) && $sessions->count() === 1) disabled @endif>
                <option value="">Toutes les sessions...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" @if(isset($selectedSession) && $selectedSession == $session->id) selected @endif>{{ $session->nom }}</option>
                @endforeach
            </select>
            @if(isset($sessions) && $sessions->count() === 1)
                <input type="hidden" name="session_id" value="{{ $sessions->first()->id }}">
            @endif
        </div>
        <div class="col-md-3">
            <label for="classe_id" class="form-label">Classe</label>
            <select name="classe_id" id="classe_id" class="form-select" required @if(isset($classes) && $classes->count() === 1) disabled @endif>
                <option value="">Toutes les classes...</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" @if(isset($selectedClasse) && $selectedClasse == $classe->id) selected @endif>{{ $classe->nom }}</option>
                @endforeach
            </select>
            @if(isset($classes) && $classes->count() === 1)
                <input type="hidden" name="classe_id" value="{{ $classes->first()->id }}">
            @endif
        </div>
        <div class="col-md-3">
            <label for="evaluation_id" class="form-label">Évaluation</label>
            <select name="evaluation_id" id="evaluation_id" class="form-select" required>
                <option value="">Toutes les évaluations...</option>
                @foreach($evaluations as $eval)
                    <option value="{{ $eval->numero_eval }}" @if(isset($selectedEvaluation) && $selectedEvaluation == $eval->numero_eval) selected @endif>
                        {{ $eval->trimestre }} - UA {{ $eval->numero_eval }} ({{ $eval->date_eval }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </div>
    </form>

    @if($registres && count($registres))
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sous-compétence</th>
                    <th>Modalité</th>
                    <th>Accès</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registres as $reg)
                    <tr>
                        <td>{{ $reg->sousCompetence->nom ?? '' }}</td>
                        <td>{{ $reg->modalite->nom ?? '' }}</td>
                        <td>
                            <a href="{{ route('registre.show', ['session_id' => $selectedSession, 'classe_id' => $selectedClasse, 'evaluation' => $selectedEvaluation]) }}?sous_competence_id={{ $reg->sous_competence_id }}&modalite_id={{ $reg->modalite_id }}" class="btn btn-sm btn-info">Voir le registre</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($selectedSession && $selectedClasse && $selectedEvaluation)
        <div class="alert alert-warning">Aucun registre trouvé pour ces critères.</div>
    @else
        <div class="alert alert-secondary">Veuillez sélectionner une session, une classe et une évaluation pour afficher les registres existants.</div>
    @endif
</div>
@endsection
