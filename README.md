@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif
@if(isset($pdfMode) && $pdfMode)
    <style>
        @page { size: A4 landscape; margin: 0.7cm; }
        body { font-size: 10px; }
        .registre-table, .registre-table th, .registre-table td {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 9px;
            word-break: break-word;
        }
        .registre-table th, .registre-table td {
            padding: 2px 3px;
            text-align: center;
        }
        .header-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .registre-title {
            font-weight: bold;
            font-size: 13px;
            margin: 6px 0;
        }
        .subtitle {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .logo-placeholder {
            width: 70px;
            height: 50px;
            border: 1px solid #999;
            display: inline-block;
            vertical-align: middle;
            margin-left: 15px;
        }
    </style>
@else
    <style>
        .registre-table, .registre-table th, .registre-table td {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 12px;
        }
        .registre-table th, .registre-table td {
            padding: 2px 5px;
            text-align: center;
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .registre-title {
            font-weight: bold;
            font-size: 18px;
            margin: 10px 0;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .logo-placeholder {
            width: 100px;
            height: 80px;
            border: 1px solid #999;
            display: inline-block;
            vertical-align: middle;
            margin-left: 30px;
        }
    </style>
@endif
<div class="container" id="registre-pdf">
    <div class="header-section">
        <div>REPUBLIQUE DU CAMEROUN</div>
        <div>PAIX - TRAVAIL - PATRIE</div>
        <div>MINISTERE DE L'EDUCATION DE BASE
            <span class="logo-placeholder">LOGO</span>
        </div>
        <div class="registre-title">REGISTRE DE NOTES</div>
        <div class="subtitle">Année scolaire : <b>{{ $session->nom ?? '' }}</b> | Classe : <b>{{ $classe->nom ?? '' }}</b></div>
    </div>
    @if(empty($modalites) || count($modalites) === 0)
    <div class="alert alert-warning">Aucune modalité n'est définie pour ce registre. Merci de vérifier la configuration des compétences/modalités.</div>
@else
<table class="registre-table" width="100%">
    <thead>
        <tr>
            <th rowspan="3">#</th>
            
            <th rowspan="3">Nom</th>
            @php $colIdx = 0; $stop = false; @endphp
@foreach($ordreCompetences as $nom)
    @if($stop) @break @endif
    @if(isset($competencesMap[strtoupper($nom)]))
        @php
            $colspanTotal = 0;
            foreach($competencesMap[strtoupper($nom)] as $scIdx => $sousCompetence) {
                $colspanTotal += isset($colonnes[$colIdx]['colspan']) ? $colonnes[$colIdx]['colspan'] : 1;
                if(strtoupper($sousCompetence->nom) === 'ACTIVITE ARTISTIQUE') { $stop = true; break; }
                $colIdx++;
            }
        @endphp
        <th colspan="{{ $colspanTotal }}">{{ $nom }}</th>
    @endif
@endforeach
            <th rowspan="3">Moyenne</th>
            <th rowspan="3">Cote</th>
            <th rowspan="3">Rang</th>
        </tr>
        <tr>
            @php $colIdx = 0; $stop = false; @endphp
@foreach($ordreCompetences as $nom)
    @if($stop) @break @endif
    @if(isset($competencesMap[strtoupper($nom)]))
        @foreach($competencesMap[strtoupper($nom)] as $scIdx => $sousCompetence)
            @if(strtoupper($sousCompetence->nom) === 'ACTIVITE ARTISTIQUE')
                <th colspan="{{ isset($colonnes[$colIdx]['colspan']) ? $colonnes[$colIdx]['colspan'] : 1 }}">{{ $sousCompetence->nom }}</th>
                @php $stop = true; @endphp
                @break
            @endif
            <th colspan="{{ isset($colonnes[$colIdx]['colspan']) ? $colonnes[$colIdx]['colspan'] : 1 }}">{{ $sousCompetence->nom }}</th>
            @php $colIdx++; @endphp
        @endforeach
    @endif
@endforeach
        </tr>
        <tr>
            @php $colIdx = 0; $stop = false; @endphp
@foreach($ordreCompetences as $nom)
    @if($stop) @break @endif
    @if(isset($competencesMap[strtoupper($nom)]))
        @foreach($competencesMap[strtoupper($nom)] as $scIdx => $sousCompetence)
            @if(isset($sous_colonnes[$colIdx]))
                @foreach($sous_colonnes[$colIdx] as $modaliteNom)
                    <th>{{ $modaliteNom }}</th>
                @endforeach
            @endif
            @if(strtoupper($sousCompetence->nom) === 'ACTIVITE ARTISTIQUE')
                @php $stop = true; @endphp
                @break
            @endif
            @php $colIdx++; @endphp
        @endforeach
    @endif
@endforeach
        </tr>

    </thead>
    <tbody>
    @forelse($eleves as $eleve)
    <tr>
        <td>{{ $loop->iteration }}</td>
        
        <td>{{ $eleve->nom }}</td>
        @php $colIdx = 0; $stop = false; @endphp
@foreach($ordreCompetences as $nom)
    @if($stop) @break @endif
    @if(isset($competencesMap[strtoupper($nom)]))
        @foreach($competencesMap[strtoupper($nom)] as $scIdx => $sousCompetence)
            @if(isset($sous_colonnes[$colIdx]))
                @foreach($sous_colonnes[$colIdx] as $mIdx => $modaliteNom)
                    <td>
                        @php
                            $scId = $sousCompetence->id;
                            // On retrouve le vrai modalite_id depuis le pivot pour cette sous-compétence et ce mIdx
                            $modaliteIdsForSC = $pivot->where('sous_competence_id', $scId)->pluck('modalite_id')->values();
                            $modaliteId = isset($modaliteIdsForSC[$mIdx]) ? $modaliteIdsForSC[$mIdx] : null;
                            $noteId = $noteVal = '';
                            if ($modaliteId) {
                                $noteId = $eleve->notes_ids_map[$scId][$modaliteId] ?? '';
                                $noteVal = $eleve->notes_map[$scId][$modaliteId] ?? '';
                            }
                        @endphp
                        @if($noteId)
                            <input type="number" value="{{ $noteVal }}" step="any"
    data-eleve-id="{{ $eleve->id }}"
    data-sc-id="{{ $scId }}"
    data-modalite-id="{{ $modaliteId }}"
    data-session-id="{{ $session->id }}"
    data-classe-id="{{ $classe->id }}"
    data-trimestre="{{ $trimestre }}"
/>
                        @elseif($scId && $modaliteId)
                            <input type="number" value="{{ $noteVal }}" step="any"
    data-eleve-id="{{ $eleve->id }}"
    data-sc-id="{{ $scId }}"
    data-modalite-id="{{ $modaliteId }}"
    data-session-id="{{ $session->id }}"
    data-classe-id="{{ $classe->id }}"
    data-trimestre="{{ $trimestre }}"
/>
                        @else
                            <!-- Cellule vide -->
                        @endif
                    </td>
                @endforeach
            @endif
            @if(strtoupper($sousCompetence->nom) === 'ACTIVITE ARTISTIQUE')
                @php $stop = true; @endphp
                @break
            @endif
            @php $colIdx++; @endphp
        @endforeach
    @endif
@endforeach
        <td class="moyenne"></td>
        <td class="cote"></td>
        <td class="rang"></td>
    </tr>
    @empty
    <tr><td colspan="60">Aucun élève</td></tr>
    @endforelse
</tbody>
</table>

@endif

@if(!isset($pdfMode) || !$pdfMode)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX sauvegarde immédiate des notes
    function showToast(message, success = true) {
        let toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + (success ? 'success' : 'danger') + ' border-0 position-fixed top-0 end-0 m-3';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `<div class='d-flex'><div class='toast-body'>${message}</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button></div>`;
        document.body.appendChild(toast);
        let bsToast = new bootstrap.Toast(toast, {delay: 2500});
        bsToast.show();
        bsToast._element.addEventListener('hidden.bs.toast', function() { toast.remove(); });
    }
    document.querySelectorAll('.registre-table input[type=number]').forEach(input => {
        console.log('Attachement AJAX sur input', input);
        input.addEventListener('change', function() {
            let el = this;
            let data = el.dataset;
            let valeur = el.value;
            fetch("{{ route('registre.ajaxNote') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    eleve_id: data.eleveId,
                    sous_competence_id: data.scId,
                    modalite_id: data.modaliteId,
                    session_id: data.sessionId,
                    classe_id: data.classeId,
                    trimestre: data.trimestre,
                    valeur: valeur
                })
            }).then(r=>r.json()).then(res => {
                showToast(res.message, res.success);
                if(res.success && res.note_id) {
                    // Optionnel : mettre à jour l'attribut name si c'était un new_*
                }
            }).catch(()=>{
                showToast('Erreur lors de la sauvegarde', false);
            });
        });
    });
});
</script>
@endif

@if(!isset($pdfMode) || !$pdfMode)
    <form action="{{ route('registre.updateNotes') }}" method="POST">
    <input type="hidden" name="trimestre" value="{{ $trimestre }}">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        <input type="hidden" name="classe_id" value="{{ $classe->id }}">
        <input type="hidden" name="trimestre" value="{{ $trimestre }}">
        <div class="mt-3">
            <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
        </div>
    </form>
@endif
<div class="mt-4">
    <a href="{{ route('registre.exportPdf', [$session->id, $classe->id, $trimestre]) }}" class="btn btn-primary">Imprimer / Exporter PDF</a>
    <a href="{{ route('registre.exportExcel', [$session->id, $classe->id, $trimestre]) }}" class="btn btn-success ms-2">Exporter Excel</a>
    <a href="{{ route('classes.index') }}" class="btn btn-secondary ms-2">Retour</a>
</div>
</div>
<script>
// Calcul automatique des moyennes, cotes et rangs
document.addEventListener('DOMContentLoaded', function() {
    function getCote(moyenne) {
        if (moyenne >= 16) return 'A';
        if (moyenne >= 14) return 'B';
        if (moyenne >= 12) return 'C';
        if (moyenne >= 10) return 'D';
        if (moyenne > 0) return 'F';
        return '';
    }
    function updateMoyennesEtClassement() {
        let lignes = document.querySelectorAll('.registre-table tbody tr');
        let moyennes = [];
        lignes.forEach((tr, idx) => {
            let notes = Array.from(tr.querySelectorAll('input[type=number]'));
            let total = 0, count = 0;
            notes.forEach(input => {
                let val = parseFloat(input.value);
                if (!isNaN(val)) {
                    total += val;
                    count++;
                }
            });
            let moyenne = count > 0 ? (total / count) : 0;
            moyennes.push({idx, moyenne});
            let cellMoyenne = tr.querySelector('.moyenne');
            let cellCote = tr.querySelector('.cote');
            if (cellMoyenne) cellMoyenne.textContent = moyenne > 0 ? moyenne.toFixed(2) : '';
            if (cellCote) cellCote.textContent = moyenne > 0 ? getCote(moyenne) : '';
        });
        // Classement
        let sorted = [...moyennes].sort((a, b) => b.moyenne - a.moyenne);
        let rangs = Array(moyennes.length).fill(0);
        let lastMoy = null, lastRank = 1;
        sorted.forEach((item, i) => {
            if (lastMoy !== null && item.moyenne < lastMoy) lastRank = i + 1;
            rangs[item.idx] = lastRank;
            lastMoy = item.moyenne;
        });
        lignes.forEach((tr, idx) => {
            let cellRang = tr.querySelector('.rang');
            if (cellRang) cellRang.textContent = rangs[idx] > 0 ? rangs[idx] : '';
        });
    }
    document.querySelectorAll('.registre-table input[type=number]').forEach(input => {
        input.addEventListener('input', updateMoyennesEtClassement);
    });
    updateMoyennesEtClassement();
});
</script>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    let toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<div class='d-flex'><div class='toast-body'>{{ session('success') }}</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button></div>`;
    document.body.appendChild(toast);
    let bsToast = new bootstrap.Toast(toast, {delay: 3500});
    bsToast.show();
});
</script>
@endif

<div class="mt-4">
    <a href="{{ route('registre.exportPdf', [$session->id, $classe->id, $trimestre]) }}" class="btn btn-primary">Imprimer / Exporter PDF</a>
    <a href="{{ route('registre.exportExcel', [$session->id, $classe->id, $trimestre]) }}" class="btn btn-success ms-2">Exporter Excel</a>
    <a href="{{ route('classes.index') }}" class="btn btn-secondary ms-2">Retour</a>
</div>
@endsection
