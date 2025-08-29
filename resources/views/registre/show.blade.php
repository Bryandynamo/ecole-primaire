@extends('layouts.app')

@section('content')

{{-- DEBUG : Affichage brut de la synthèse pour contrôle --}}
@if(isset($synthese))
    <pre style="background:#f7f7f7;font-size:12px;border:1px solid #ccc;max-height:300px;overflow:auto;">{{ json_encode($synthese, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
@endif

<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>
<div class="d-flex justify-content-end mb-3 gap-2">
    <a href="{{ route('couverture.show', ['classe' => $classe->id, 'evaluation' => $evaluationId ?? ($evaluation->id ?? 1)]) }}" class="btn btn-dark noprint">
        <i class="bi bi-journal-richtext"></i> Couverture
    </a>
    <a href="{{ route('couverture.recapitulatif', ['classe' => $classe->id]) }}" class="btn btn-info noprint">
        <i class="bi bi-table"></i> Récapitulatif Couverture
    </a>
    <button id="btn-bulletin" class="btn btn-success noprint">
        <i class="bi bi-file-earmark-pdf"></i> Bulletins
    </button>
    <button id="btn-statistiques" class="btn btn-danger">
        <i class="bi bi-bar-chart"></i> Statistiques
    </button>
    <a href="{{ route('registre.syntheseCompetencesPage', [$session->id, $classe->id, $evaluationId ?? ($evaluation->id ?? 1)]) }}" class="btn btn-warning" style="color: #000;">
        <i class="bi bi-graph-up"></i> Synthèse des compétences
    </a>
    
</div>

<!-- Modal Bulletin -->
<div class="modal fade" id="modalBulletin" tabindex="-1" aria-labelledby="modalBulletinLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBulletinLabel">Choix du bulletin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous générer le bulletin pour&nbsp;:</p>
        <div class="d-grid gap-2">
          <button id="bulletin-ua" class="btn btn-outline-primary">Cette UA uniquement</button>
<button id="bulletin-trimestre" class="btn btn-outline-secondary">Le trimestre de cette UA</button>
<button id="bulletin-annuel" class="btn btn-outline-success"
        onclick="window.open('{{ route('bulletins.annuels', ['classe' => $classe->id]) }}', '_blank')">
    Bulletin annuel (année complètez)
</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Statistiques -->
<div class="modal fade" id="modalStatistiques" tabindex="-1" aria-labelledby="modalStatistiquesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalStatistiquesLabel">Choix des statistiques</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous voir les statistiques pour&nbsp;:</p>
        <div class="d-grid gap-2">
    <button id="stats-ua" class="btn btn-outline-primary">Cette UA uniquement</button>
    <button id="stats-trimestre" class="btn btn-outline-secondary">Le trimestre de cette UA</button>
    <button id="stats-annuel" class="btn btn-outline-success">Annuel (toute l'année)</button>
</div>
      </div>
    </div>
  </div>
</div>

@php
    $trimestreLabel = '';
    if(isset($evaluation)) {
        $trimestreLabel = 'UA ' . $evaluation;
    }
    
    // Ajouter les UAs correspondantes si c'est un trimestre
    if(isset($trimestre) && isset($uas) && !empty($uas)) {
        $uasList = implode(', ', array_map(function($ua) { return 'UA ' . $ua; }, $uas));
        $trimestreLabel .= ' (' . $uasList . ')';
    }
@endphp

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
        .registre-table {
            border-collapse: collapse;
            width: 100%;
        }
        .registre-table th,
        .registre-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            text-align: center;
            font-size: 12px;
        }
        .registre-table th {
            background-color: #f8f9fa;
        }
        .total-souscompetence {
            background-color: #fff9c4;
        }
        .rang {
            font-weight: bold;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .toast-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .toast-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
        const SESSION_ID = @json($session->id);
        const CLASSE_ID = @json($classe->id);
        const EVALUATION_ID = @json($evaluationId ?? ($evaluation->id ?? null));
        
        // Vérifier que EVALUATION_ID est valide
        if (!EVALUATION_ID) {
            console.error('EVALUATION_ID est null ou invalide:', EVALUATION_ID);
            alert('Erreur: Évaluation non trouvée. Veuillez recharger la page.');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour afficher les notifications (toast)
            function showToast(message, type = 'success') {
                const toastContainer = document.getElementById('toast-container');
                if (!toastContainer) return;

                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show`;
                toast.role = 'alert';
                toast.ariaLive = 'assertive';
                toast.ariaAtomic = 'true';

                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;

                toastContainer.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
                bsToast.show();
                toast.addEventListener('hidden.bs.toast', () => toast.remove());
            }

            // Fonction de debounce pour la sauvegarde
            let debounceTimer;
            function debounce(func, delay) {
                return function(...args) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => func.apply(this, args), delay);
                };
            }

            // Fonction pour sauvegarder une note
            const saveNote = debounce(function(input) {
                // Vérifier que EVALUATION_ID est valide avant d'envoyer la requête
                if (!EVALUATION_ID) {
                    showToast('Erreur: Évaluation non trouvée. Veuillez recharger la page.', 'error');
                    return;
                }
                
                const noteData = {
                    eleve_id: input.data('eleve-id'),
                    sc_id: input.data('sc-id'),
                    modalite_id: input.data('modalite-id'),
                    session_id: '{{ $session->id }}',
                    classe_id: '{{ $classe->id }}',
                    evaluation_id: EVALUATION_ID,
                    valeur: input.val(),
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route('registre.updateNotes') }}',
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
            }, 500); // Sauvegarde 500ms après la dernière frappe

            // Fonction pour mettre à jour tous les calculs pour une ligne d'élève
            function updateAllCalculations(eleveRow) {
                let grandTotalEleve = 0;
                let totalPointsMaxEleve = 0;

                // Recalculer les totaux de chaque sous-compétence
                eleveRow.find('.total-souscompetence').each(function() {
                    const scCell = $(this);
                    const scLabel = scCell.data('sc-label');
                    let totalSc = 0;
                    eleveRow.find(`input[data-sc-label='${scLabel}']`).each(function() {
                        const val = parseFloat($(this).val());
                        if (!isNaN(val)) {
                            totalSc += val;
                        }
                    });
                    scCell.text(totalSc.toFixed(2));
                    grandTotalEleve += totalSc;
                });

                // Recalculer le total des points max pour l'élève
                eleveRow.find('.total-souscompetence').each(function() {
                    totalPointsMaxEleve += parseFloat($(this).data('points-max-sc'));
                });

                // Mettre à jour le total général
                eleveRow.find('.total-general').text(grandTotalEleve.toFixed(2));

                // Mettre à jour la moyenne
                const moyenneCell = eleveRow.find('.moyenne');
                const moyenne = totalPointsMaxEleve > 0 ? (grandTotalEleve / totalPointsMaxEleve) * 20 : 0;
                moyenneCell.text(moyenne.toFixed(2));

                // Mettre à jour la cote
                const coteCell = eleveRow.find('.cote');
                let cote = 'E';
                if (moyenne >= 16) cote = 'A';
                else if (moyenne >= 14) cote = 'B';
                else if (moyenne >= 12) cote = 'C';
                else if (moyenne >= 10) cote = 'D';
                coteCell.text(cote);

                // Mettre à jour les rangs après un court délai pour s'assurer que tous les calculs sont terminés
                setTimeout(updateRanks, 100);
            }

            // Fonction pour mettre à jour les rangs de tous les élèves
            function updateRanks() {
                const eleves = [];
                $('.registre-table tbody tr').each(function() {
                    const row = $(this);
                    const moyenne = parseFloat(row.find('.moyenne').text());
                    if (!isNaN(moyenne)) {
                        eleves.push({ row, moyenne });
                    }
                });

                eleves.sort((a, b) => b.moyenne - a.moyenne);

                eleves.forEach((eleve, index) => {
                    eleve.row.find('.rang').text(index + 1);
                });
            }

            // Écouteur d'événement sur les inputs de notes
            $('.note-input').on('input', function() {
                const input = $(this);
                const eleveRow = input.closest('tr');
                updateAllCalculations(eleveRow);
                saveNote(input);
            });

            // Calculs initiaux au chargement de la page
            $('.registre-table tbody tr').each(function() {
                updateAllCalculations($(this));
            });
        });
    </script>
@endif
<div class="container" id="registre-pdf">
    <div class="header-section">
        <div>REPUBLIQUE DU CAMEROUN</div>
        <div>PAIX - TRAVAIL - PATRIE</div>
        <div>MINISTERE DE L'EDUCATION DE BASE
            <span class="logo-placeholder">LOGO</span>
        </div>
        <div class="registre-title">REGISTRE DE NOTES</div>
        <div class="subtitle">Année scolaire : <b>{{ $session->nom ?? '' }}</b> | Classe : <b>{{ $classe->nom ?? '' }}</b> | Évaluation : <b>{{ $trimestreLabel }}</b></div>
    </div>

<div style="overflow-x:auto;"><div class="table-responsive">
<table class="registre-table" style="font-size:11px;min-width:1200px;width:auto;">
    <thead>
        <!-- Row 1: Competence Label -->
        <tr>
            <th rowspan="4">#</th>
            <th rowspan="4">Matricule</th>
            <th rowspan="4">Nom et prénom</th>
            @foreach($ordreCompetences as $comp)
                @php
                    $compSpan = 0;
                    foreach($comp['sous'] as $sc) {
                        // +1 for total_sc column
                        $compSpan += count($sc['modalites']) + 1;
                    }
                @endphp
                <th colspan="{{ $compSpan }}">{{ $comp['label'] }}</th>
            @endforeach
            <th rowspan="4">Total</th>
            <th rowspan="4">Moyenne/20</th>
            <th rowspan="4">Cote</th>
            <th rowspan="4">Rang</th>
        </tr>

        <!-- Row 2: Sous-Competence Label -->
        <tr>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    <th colspan="{{ count($sc['modalites']) + 1 }}">{{ $sc['label'] }}</th>
                @endforeach
            @endforeach
        </tr>

        <!-- Row 3: Modalite Label -->
        <tr>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    @foreach($sc['modalites'] as $modaliteNom)
                        <th>{{ $modaliteNom }}</th>
                    @endforeach
                    <th>Total SC</th>
                @endforeach
            @endforeach
        </tr>

        <!-- Row 4: Points Max -->
        <tr>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    @php $totalPointsMaxSc = 0; @endphp
                    @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                        @php
                            $points = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                            $totalPointsMaxSc += $points;
                        @endphp
                        <th>/{{ $points }}</th>
                    @endforeach
                    <th>/{{ $totalPointsMaxSc }}</th>
                @endforeach
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($eleves as $eleve)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $eleve->matricule }}</td>
                <td>{{ $eleve->nom }} {{ $eleve->prenom }}</td>
                
                @php
                    $grandTotalEleve = 0;
                    $totalPointsMaxEleve = 0;
                @endphp

                @foreach($ordreCompetences as $comp)
                    @foreach($comp['sous'] as $sc)
                        @php
                            $scId = $sousCompetenceIds[$sc['label']] ?? null;
                            $totalSousComp = 0;
                            $totalPointsMaxSousComp = 0;
                        @endphp

                        @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                            @php
                                $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                                $val = '';
                                $pointsMax = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                                
                                if ($scId && $modaliteId && isset($eleve->notes_map[$scId]) && isset($eleve->notes_map[$scId][$modaliteId])) {
                                    $val = $eleve->notes_map[$scId][$modaliteId];
                                    if (is_numeric($val)) {
                                        $totalSousComp += $val;
                                    }
                                }
                                $totalPointsMaxSousComp += $pointsMax;
                            @endphp
                            <td>
                                @if($scId && $modaliteId)
                                    <input type="number" 
                                           value="{{ $val }}" 
                                           step="any" 
                                           min="0"
                                           max="{{ $pointsMax }}"
                                           data-eleve-id="{{ $eleve->id }}"
                                           data-sc-id="{{ $scId }}"
                                           data-modalite-id="{{ $modaliteId }}"
                                           data-session-id="{{ $session->id }}"
                                           data-classe-id="{{ $classe->id }}"
                                           data-evaluation-id="{{ $evaluationId }}"
                                           data-sc-label="{{ $sc['label'] }}"
                                           class="note-input"
                                           style="width: 50px;">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Total Sous-compétence --}}
                        <td class="total-souscompetence" 
                            data-sc-label="{{ $sc['label'] }}" 
                            data-points-max-sc="{{ $totalPointsMaxSousComp }}">{{ $totalSousComp }}</td>
                        
                        @php
                            $grandTotalEleve += $totalSousComp;
                            $totalPointsMaxEleve += $totalPointsMaxSousComp;
                        @endphp
                    @endforeach
                @endforeach

                {{-- Colonnes finales --}}
                <td class="total-general">{{ $grandTotalEleve }}</td>
                <td class="moyenne">
                    @if($totalPointsMaxEleve > 0)
                        {{ number_format(($grandTotalEleve / $totalPointsMaxEleve) * 20, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="cote">-</td>
                <td class="rang">-</td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">Aucun élève trouvé pour cette classe.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div></div>

@if(isset($pdfMode) && $pdfMode)
<style>
  @page { size: A4 landscape; }
  table { font-size: 10px; }
  th, td { padding: 2px 4px; }
</style>
@endif
@if(!isset($pdfMode) || !$pdfMode)
<div class="mt-4">
    <a href="{{ route('registre.exportPdf', [$session->id, $classe->id, $evaluation]) }}" class="btn btn-primary">Imprimer / Exporter PDF</a>
    <a href="{{ route('registre.exportExcel', [$session->id, $classe->id, $evaluation]) }}" class="btn btn-success ms-2">Exporter Excel</a>
    <a href="{{ route('registre.index') }}" class="btn btn-secondary ms-2">Retour</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulletin modal
    const btnBulletin = document.getElementById('btn-bulletin');
    const modalBulletin = new bootstrap.Modal(document.getElementById('modalBulletin'));
    btnBulletin.addEventListener('click', function(e) {
        e.preventDefault();
        modalBulletin.show();
    });
    document.getElementById('bulletin-ua').addEventListener('click', function() {
        window.location.href = "{{ route('bulletins.evaluation', [$session->id, $classe->id, $evaluation ?? 1]) }}";
    });
    document.getElementById('bulletin-trimestre').addEventListener('click', function() {
        let ua = {{ $evaluation ?? 1 }};
        let trimestre = Math.ceil(ua / 3);
        window.location.href = "{{ route('bulletins.trimestre', [$session->id, $classe->id]) }}?trimestre=" + trimestre;
    });

    // Statistiques modal (déjà présent)
    const btnStats = document.getElementById('btn-statistiques');
    const modal = new bootstrap.Modal(document.getElementById('modalStatistiques'));
    btnStats.addEventListener('click', function(e) {
        e.preventDefault();
        modal.show();
    });
    document.getElementById('stats-ua').addEventListener('click', function() {
        let ua = {{ $evaluation ?? 1 }};
        window.location.href = "/registre/{{ $session->id }}/{{ $classe->id }}/statistiques/" + ua + "/ua";
    });
    document.getElementById('stats-trimestre').addEventListener('click', function() {
        let ua = {{ $evaluation ?? 1 }};
        let trimestre = Math.ceil(ua / 3);
        window.location.href = "/registre/{{ $session->id }}/{{ $classe->id }}/statistiques/" + trimestre + "/trimestre";
    });
    document.getElementById('stats-annuel').addEventListener('click', function() {
        window.location.href = "/registre/{{ $session->id }}/{{ $classe->id }}/statistiques/annee";
    });
});
</script>
@endif
@endsection
