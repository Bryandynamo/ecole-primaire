@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h4>SESSION {{ $session->nom }}</h4>
        <div class="row">
            <div class="col-md-4">Evaluation numero: <span class="evaluation-num">{{ $trimestre }}</span></div>
            <div class="col-md-4">Mois de: <span class="mois">UA {{ $trimestre }}</span></div>
        </div>
    </div>

    <form action="{{ route('registre.generer') }}" method="POST">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session_id }}">
        <input type="hidden" name="classe_id" value="{{ $classe_id }}">
        <input type="hidden" name="evaluation" value="{{ $trimestre }}">

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <!-- En-tête des compétences -->
                    <tr>
                        <th rowspan="4" style="vertical-align: middle;">N°</th>
                        <th rowspan="4" style="vertical-align: middle;">Nom de l'élève</th>
                        @foreach($competences as $competence)
                            @php
                                $firstSousCompetence = $competence->sousCompetences->first();
                                $hideCompetence = ($competence->nom === "COMPETENCE6" && $firstSousCompetence && $firstSousCompetence->nom === "6B.ACTIVITES ARTISTIQUE");
                            @endphp
                            @if(!$hideCompetence)
                                @php
                                    $colspan = 0;
                                    foreach($competence->sousCompetences as $sc) {
                                        $colspan += count($sc->modalites) + 1;
                                    }
                                @endphp
                                <th colspan="{{ $colspan }}">{{ $competence->nom }}</th>
                            @endif
                        @endforeach
                        <th rowspan="4" style="vertical-align: middle;">Moyenne/20</th>
                        <th rowspan="4" style="vertical-align: middle;">Cote</th>
                        <th rowspan="4" style="vertical-align: middle;">Rang</th>
                    </tr>

                    <!-- En-tête des sous-compétences -->
                    <tr>
                        @foreach($ordreCompetences as $comp)
                            @foreach($comp['sous'] as $scIdx => $sc)
                                @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                                    <th>{{ $modaliteNom }}</th>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tr>

                    <!-- Ligne des points maximum -->
                    <tr class="table-secondary">
                        @foreach($ordreCompetences as $comp)
                            @foreach($comp['sous'] as $scIdx => $sc)
                                @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                                    <th class="max-points">{{ $sc['points_max'][$mIdx] ?? 20 }}</th>
                                                $total_max_sc += $scm ? $scm->points_max : 0;
                                            }
                                            echo $total_max_sc;
                                        @endphp
                                    </th>
                                @endforeach
                            @endif
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($eleves as $eleve)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $eleve->nom }}</td>
                            @php 
                                $grand_total = 0;
                                $total_max_points = 0;
                            @endphp
                            @foreach($ordreCompetences as $comp)
                                @foreach($comp['sous'] as $scIdx => $sc)
                                    @php 
                                        $total_sc = 0;
                                        $total_max_sc = 0;
                                        $scId = $sousCompetenceIds[$sc['label']] ?? null;
                                    @endphp
                                    @if($scId)
                                        @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                                            @php
                                                $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                                                if ($modaliteId) {
                                                    $note_value = isset($eleve->notes_map[$scId][$modaliteId]) ? $eleve->notes_map[$scId][$modaliteId] : '';
                                                    if(is_numeric($note_value)) {
                                                        $total_sc += $note_value;
                                                    }
                                                    $total_max_sc += $sc['points_max'][$mIdx] ?? 20;
                                                }
                                            @endphp
                                            <td>
                                                @if($scId && $modaliteId)
                                                    <input type="number" 
                                                        name="notes[{{ $eleve->id }}][{{ $scId }}][{{ $modaliteId }}]" 
                                                        class="form-control form-control-sm note-input"
                                                        step="0.01" 
                                                        min="0" 
                                                        max="{{ $sc['points_max'][$mIdx] ?? 20 }}" 
                                                        value="{{ $note_value }}"
                                                        data-eleve-id="{{ $eleve->id }}"
                                                        data-sc-id="{{ $scId }}"
                                                        data-modalite-id="{{ $modaliteId }}"
                                                        data-session-id="{{ $session->id }}"
                                                        data-classe-id="{{ $classe->id }}"
                                                        data-evaluation-id="{{ $trimestre }}"
                                                        style="width: 60px;"
                                                    >
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @php 
                                            $grand_total += $total_sc;
                                            $total_max_points += $total_max_sc;
                                        @endphp
                                        <td class="table-secondary total-sc" data-max="{{ $total_max_sc }}">
                                            @if($total_sc > 0)
                                                {{ number_format($total_sc, 2) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            @endforeach
                            <td class="moyenne">
                                @if($total_max_points > 0)
                                    {{ number_format($grand_total / $total_max_points * 20, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="cote">
                                @php
                                
                                    $moyenne = $total_max_points > 0 ? $grand_total / $total_max_points * 20 : 0;
                                @endphp
                                 @if($moyenne >= 18) A+
                                @elseif($moyenne >= 16) A
                                @elseif($moyenne >= 14) B+
                                @elseif($moyenne >= 12) B
                                @elseif($moyenne >= 10) C+
                                @elseif($moyenne >= 8) C
                                @elseif($moyenne >= 6) D+
                                @elseif($moyenne >= 4) D
                                @else E
                                @endif
                            </td>
                            <td class="rang"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <div class="row g-2">
  <div class="col-12 col-md-6">
    <button type="submit" class="btn btn-success w-100 mb-2">Enregistrer les notes</button>
  </div>
  <div class="col-12 col-md-6">
    <a href="{{ route('registre.index') }}" class="btn btn-secondary w-100 mb-2">Retour</a>
  </div>
</div>
        </div>
    </form>
</div>

<style>
.table-responsive {
    overflow-x: auto;
}
.table {
    font-size: 0.875rem;
}
.form-control-sm {
    height: 25px;
    padding: 2px 5px;
}
th {
    white-space: nowrap;
    background-color: #f8f9fa;
}
.max-points, .total-max {
    font-weight: bold;
    color: #495057;
}
</style>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
showToast('Auto-save JS chargé', 'success');
console.log('Auto-save JS chargé');
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour les calculs quand une note change
    document.querySelectorAll('.note-input').forEach(input => {
        input.addEventListener('change', function() {
            calculerTotaux();
        });
    });

    // Calcul initial
    calculerTotaux();

    // --- AUTO-SAVE AJAX ---
    function showToast(message, type = 'success') {
        let toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0 show';
        toast.role = 'alert';
        toast.ariaLive = 'assertive';
        toast.ariaAtomic = 'true';
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = 1000;
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 3000);
    }

    let debounceTimer;
    function debounce(func, delay) {
        return function(...args) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Fonction pour sauvegarder une note en AJAX
    const saveNote = debounce(function(input) {
        const noteData = {
            eleve_id: input.getAttribute('data-eleve-id'),
            sc_id: input.getAttribute('data-sc-id'),
            modalite_id: input.getAttribute('data-modalite-id'),
            session_id: input.getAttribute('data-session-id'),
            classe_id: input.getAttribute('data-classe-id'),
            evaluation_id: input.getAttribute('data-evaluation-id'),
            valeur: input.value,
            _token: document.querySelector('input[name=_token]').value
        };
        $.ajax({
            url: '/registre/updateNotes',
            method: 'POST',
            data: { notes: [noteData] },
            success: function(response) {
                showToast(response.message || 'Note sauvegardée.');
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Erreur de sauvegarde.';
                showToast(errorMsg, 'error');
            }
        });
    }, 500);

    // Lier l'auto-save sur chaque input de note
    document.querySelectorAll('.note-input').forEach(input => {
        input.addEventListener('input', function() {
            saveNote(input);
        });
    });

    function calculerTotaux() {
        // Calculer d'abord les totaux maximum pour chaque sous-compétence
        document.querySelectorAll('.total-max').forEach(totalMaxCell => {
            let maxPoints = 0;
            let cells = totalMaxCell.parentElement.querySelectorAll('.max-points');
            cells.forEach(cell => {
                maxPoints += parseFloat(cell.textContent) || 0;
            });
            totalMaxCell.textContent = maxPoints;
        });

        // Calculer les totaux pour chaque élève
        document.querySelectorAll('tbody tr').forEach(tr => {
            let grandTotal = 0;
            let totalMaxPoints = 0;

            // Calculer les totaux par sous-compétence
            tr.querySelectorAll('.total-sc').forEach(totalCell => {
                let total = 0;
                let inputs = Array.from(totalCell.parentElement.querySelectorAll('input[type="number"]'));
                inputs.forEach(input => {
                    let val = parseFloat(input.value);
                    if (!isNaN(val)) total += val;
                });
                totalCell.textContent = total > 0 ? total.toFixed(2) : '';
                
                // Ajouter au total général
                grandTotal += total;
                let maxPoints = 0;
                inputs.forEach(input => {
                    maxPoints += parseFloat(input.getAttribute('max')) || 0;
                });
                totalMaxPoints += maxPoints;
            });

            // Mettre à jour le total général
            tr.querySelector('.grand-total').textContent = grandTotal > 0 ? grandTotal.toFixed(2) : '';

            // Calculer et mettre à jour la moyenne sur 20
            let moyenne = totalMaxPoints > 0 ? (grandTotal * 20) / totalMaxPoints : 0;
            tr.querySelector('.moyenne').textContent = moyenne > 0 ? moyenne.toFixed(2) : '';

            // Mettre à jour la cote
            tr.querySelector('.cote').textContent = getCote(moyenne);
        });

        // Calculer les rangs
        calculerRangs();
    }

    function getCote(moyenne) {
        if (moyenne >= 16) return 'A';
        if (moyenne >= 14) return 'B';
        if (moyenne >= 12) return 'C';
        if (moyenne >= 10) return 'D';
        if (moyenne > 0) return 'E';
        return '';
    }

    function calculerRangs() {
        let moyennes = [];
        document.querySelectorAll('tbody tr').forEach((tr, index) => {
            let moyenne = parseFloat(tr.querySelector('.moyenne').textContent);
            if (!isNaN(moyenne)) {
                moyennes.push({
                    index: index,
                    moyenne: moyenne
                });
            }
        });

        // Trier les moyennes par ordre décroissant
        moyennes.sort((a, b) => b.moyenne - a.moyenne);

        // Attribuer les rangs
        let currentRank = 1;
        let lastMoyenne = null;
        let rows = document.querySelectorAll('tbody tr');

        moyennes.forEach((item, index) => {
            if (lastMoyenne !== null && item.moyenne < lastMoyenne) {
                currentRank = index + 1;
            }
            rows[item.index].querySelector('.rang').textContent = currentRank;
            lastMoyenne = item.moyenne;
        });
    }
});
</script>
@endsection
