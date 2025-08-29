@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sélection du registre de notes</h1>
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <div class="table-responsive">
                <label for="session_id" class="form-label">Session académique</label>
            </div>
            <select name="session_id" id="session_id" class="form-select w-100" required @if(isset($sessions) && $sessions->count() === 1) disabled @endif>
                <option value="" disabled>Choisir une session...</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" @if(isset($selectedSession) && $selectedSession == $session->id) selected @endif>{{ $session->nom }}</option>
                @endforeach
            </select>
            @if(isset($sessions) && $sessions->count() === 1)
                <input type="hidden" name="session_id" value="{{ $sessions->first()->id }}">
            @endif
        </div>
        <div class="col-md-4">
            <div class="table-responsive">
                <label for="classe_id" class="form-label">Classe</label>
            </div>
            <select name="classe_id" id="classe_id" class="form-select w-100" required @if(isset($classes) && $classes->count() === 1) disabled @endif>
                <option value="" disabled>Choisir une classe...</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" @if(isset($selectedClasse) && $selectedClasse == $classe->id) selected @endif>{{ $classe->nom }}</option>
                @endforeach
            </select>
            @if(isset($classes) && $classes->count() === 1)
                <input type="hidden" name="classe_id" value="{{ $classes->first()->id }}">
            @endif
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-secondary w-100 mb-2">Charger les évaluations</button>
        </div>
        @if(isset($evaluations) && $evaluations->count())
        <div class="col-md-6">
            <div class="table-responsive">
                <label for="evaluation_id" class="form-label">Évaluation</label>
            </div>
            <select name="evaluation_id" id="evaluation_id" class="form-select w-100" required>
                <option value="" disabled selected>Choisir une évaluation...</option>
                @foreach($evaluations as $eval)
                    <option value="{{ $eval->numero_eval }}">
                        {{ $eval->trimestre }} - UA {{ $eval->numero_eval }} ({{ $eval->date_eval }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 mt-3">
            <button type="button" class="btn btn-primary w-100 mb-2" id="go-registre">Accéder au registre</button>
        </div>
        @endif
    </form>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('go-registre');
    const sessionSelect = document.getElementById('session_id');
    const classeSelect = document.getElementById('classe_id');
    const evalSelect = document.getElementById('evaluation_id');

    function updateBtnState() {
        if (btn) {
            btn.disabled = !(sessionSelect && sessionSelect.value && classeSelect && classeSelect.value && evalSelect && evalSelect.value);
        }
    }
    if (sessionSelect) sessionSelect.addEventListener('change', updateBtnState);
    if (classeSelect) classeSelect.addEventListener('change', updateBtnState);
    if (evalSelect) evalSelect.addEventListener('change', updateBtnState);
    updateBtnState();

    if(btn) {
        btn.addEventListener('click', function() {
            const session_id = sessionSelect.value;
            const classe_id = classeSelect.value;
            const evaluation_num = evalSelect.value;
            if(session_id && classe_id && evaluation_num) {
                window.location.href = `/registre/${session_id}/${classe_id}/${evaluation_num}`;
            } else {
                alert('Veuillez sélectionner une session, une classe et une évaluation avant de continuer.');
            }
        });
    }
});
</script>
@endsection
