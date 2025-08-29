<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Couverture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 text-right mb-2">
            <a href="{{ route('couverture.exportPdf', ['classe' => $classe->id, 'evaluation' => $evaluation->id]) }}" 
               class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> Exporter PDF
            </a>
        </div>
        <div class="col text-center">
            <h4 class="font-weight-bold">TAUX DE COUVERTURE</h4>
            <div>
                <strong>UA : {{ $evaluation->numero ?? $evaluation->nom }}</strong>
                &nbsp;&nbsp; Niveau : {{ $classe->niveau->nom ?? '' }}
                &nbsp;&nbsp; Classe : {{ $classe->nom }}
            </div>
        </div>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="table-secondary">
            <tr>
                <th class="text-center">N°</th>
                <th class="text-center">Discipline</th>
                <th class="text-center">Leçon</th>
                
                {{-- Colonnes pour l'UA --}}
                <th class="text-center bg-light">Prévues UA</th>
                <th class="text-center bg-light">Non couverts<br>(Précédent)</th>
                <th class="text-center bg-light">Nb. Courant</th>
                <th class="text-center bg-light">Taux Couv.</th>
                
                {{-- Colonnes pour le Trimestre --}}
                <th class="text-center">Total Trimestre</th>
                <th class="text-center">Nb. Couverts<br>(Trimestre)</th>
                <th class="text-center">Taux<br>(Trimestre)</th>
                
                {{-- Colonnes pour l'Année --}}
                <th class="text-center">Total Année</th>
                <th class="text-center">Nb. Couverts<br>(Année)</th>
                <th class="text-center">Taux<br>(Annuel)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $num = 1;
                $hasData = false;
                $sousCompetencesParDiscipline = $sousCompetences->groupBy(function($sc) {
                    return $sc->competence->nom ?? 'Autre';
                });
            @endphp
            
            @forelse($sousCompetencesParDiscipline as $discipline => $scs)
                @foreach($scs as $sc)
                    @php
                        $leconsPourSC = $lecons->get($sc->id, collect());
                        $scTotals = $totalParDiscipline[$sc->id] ?? [
                            'total_prevu_ua' => 0,
                            'nb_courant' => 0,
                            'total_trimestre' => 0,
                            'nb_couverts_trimestre' => 0,
                            'total_annee' => 0,
                            'nb_couverts_annee' => 0,
                            'taux_ua' => 0,
                            'taux_trimestre' => 0,
                            'taux_annee' => 0
                        ];
                    @endphp
                    
                    @if($leconsPourSC->isNotEmpty())
                        @php $hasData = true; @endphp
                        
                        {{-- En-tête de sous-compétence --}}
                        <tr class="table-info">
                            <td class="text-center font-weight-bold">{{ $num }}</td>
                            <td class="font-weight-bold">{{ $discipline }}</td>
                            <td class="font-weight-bold">{{ $sc->nom }}</td>
                            <td class="text-center font-weight-bold sc-total-prevu-ua">{{ $scTotals['total_prevu_ua'] }}</td>
                            <td class="text-center font-weight-bold">-</td>
                            <td class="text-center font-weight-bold sc-nb-courant">{{ $scTotals['nb_courant'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-ua">{{ number_format($scTotals['taux_ua'], 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-trimestre">{{ $scTotals['total_trimestre'] }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-trimestre">{{ $scTotals['nb_couverts_trimestre'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-trimestre">{{ number_format($scTotals['taux_trimestre'], 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-annee">{{ $scTotals['total_annee'] }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-annee">{{ $scTotals['nb_couverts_annee'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-annee">{{ number_format($scTotals['taux_annee'], 2) }}%</td>
                        </tr>
                        
                        {{-- Lignes des leçons --}}
                        @foreach($leconsPourSC as $lecon)
                            @php
                                $stats = $leconStats[$lecon->id] ?? [
                                    'total_a_couvrir_ua' => 0,
                                    'nb_non_couverts_precedent' => 0,
                                    'nb_courant' => 0,
                                    'taux_ua' => 0,
                                    'total_trimestre' => 0,
                                    'nb_couverts_trimestre' => 0,
                                    'taux_trimestre' => 0,
                                    'total_annee' => 0,
                                    'nb_couverts_annee' => 0,
                                    'taux_annee' => 0
                                ];
                            @endphp
                            <tr data-lecon-id="{{ $lecon->id }}" data-sous-competence-id="{{ $sc->id }}">
                                <td class="text-center">{{ $num }}.{{ $loop->iteration }}</td>
                                <td>{{ $discipline }}</td>
                                <td>{{ $lecon->nom }}</td>
                                <td class="text-center">{{ $stats['total_a_couvrir_ua'] }}</td>
                                <td class="text-center">{{ $stats['nb_non_couverts_precedent'] }}</td>
                                <td class="text-center">
                                    <input type="number" 
                                           class="form-control form-control-sm nb-couverts-input text-center" 
                                           data-lecon-id="{{ $lecon->id }}" 
                                           value="{{ $stats['nb_courant'] }}" 
                                           min="0" 
                                           style="width: 70px;">
                                </td>
                                <td class="text-center taux-ua">{{ number_format($stats['taux_ua'], 2) }}%</td>
                                <td class="text-center">{{ $stats['total_trimestre'] }}</td>
                                <td class="text-center nb-couverts-trimestre">{{ $stats['nb_couverts_trimestre'] }}</td>
                                <td class="text-center taux-trimestre">{{ number_format($stats['taux_trimestre'], 2) }}%</td>
                                <td class="text-center">{{ $stats['total_annee'] }}</td>
                                <td class="text-center nb-couverts-annee">{{ $stats['nb_couverts_annee'] }}</td>
                                <td class="text-center taux-annee">{{ number_format($stats['taux_annee'], 2) }}%</td>
                            </tr>
                        @endforeach
                        
                        {{-- Ligne de total pour la sous-compétence --}}
                        <tr class="table-warning total-row" data-sc-id="{{ $sc->id }}">
                            <td colspan="3" class="text-right font-weight-bold">Total {{ $sc->nom }}</td>
                            <td class="text-center font-weight-bold sc-total-prevu-ua">{{ $scTotals['total_prevu_ua'] }}</td>
                            <td class="text-center font-weight-bold">-</td>
                            <td class="text-center font-weight-bold sc-nb-courant">{{ $scTotals['nb_courant'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-ua">{{ number_format($scTotals['taux_ua'], 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-trimestre">{{ $scTotals['total_trimestre'] }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-trimestre">{{ $scTotals['nb_couverts_trimestre'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-trimestre">{{ number_format($scTotals['taux_trimestre'], 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-annee">{{ $scTotals['total_annee'] }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-annee">{{ $scTotals['nb_couverts_annee'] }}</td>
                            <td class="text-center font-weight-bold sc-taux-annee">{{ number_format($scTotals['taux_annee'], 2) }}%</td>
                        </tr>
                        
                        @php $num++; @endphp
                    @endif
                @endforeach
            @empty
                <tr>
                    <td colspan="13" class="text-center">Aucune donnée disponible pour cette classe et cette évaluation.</td>
                </tr>
            @endforelse

            @if($hasData)
                @php
                    $globalTotalPrevuUa = 0;
                    $globalTotalCourantUa = 0;
                    $globalTotalPrevuTrimestre = 0;
                    $globalTotalCouvertTrimestre = 0;
                    $globalTotalPrevuAnnee = 0;
                    $globalTotalCouvertAnnee = 0;

                    foreach($totalParDiscipline as $totals) {
                        $globalTotalPrevuUa += $totals['total_prevu_ua'];
                        $globalTotalCourantUa += $totals['nb_courant'];
                        $globalTotalPrevuTrimestre += $totals['total_trimestre'];
                        $globalTotalCouvertTrimestre += $totals['nb_couverts_trimestre'];
                        $globalTotalPrevuAnnee += $totals['total_annee'];
                        $globalTotalCouvertAnnee += $totals['nb_couverts_annee'];
                    }

                    $globalTauxUa = $globalTotalPrevuUa > 0 ? ($globalTotalCourantUa / $globalTotalPrevuUa) * 100 : 0;
                    $globalTauxTrimestre = $globalTotalPrevuTrimestre > 0 ? ($globalTotalCouvertTrimestre / $globalTotalPrevuTrimestre) * 100 : 0;
                    $globalTauxAnnee = $globalTotalPrevuAnnee > 0 ? ($globalTotalCouvertAnnee / $globalTotalPrevuAnnee) * 100 : 0;
                @endphp
                <tr class="table-dark total-global-row">
                    <td colspan="3" class="text-right font-weight-bold">Total Global</td>
                    <td class="text-center font-weight-bold global-total-prevu-ua">{{ $globalTotalPrevuUa }}</td>
                    <td class="text-center font-weight-bold">-</td>
                    <td class="text-center font-weight-bold global-nb-courant">{{ $globalTotalCourantUa }}</td>
                    <td class="text-center font-weight-bold global-taux-ua">{{ number_format($globalTauxUa, 2) }}%</td>
                    <td class="text-center font-weight-bold global-total-trimestre">{{ $globalTotalPrevuTrimestre }}</td>
                    <td class="text-center font-weight-bold global-nb-couverts-trimestre">{{ $globalTotalCouvertTrimestre }}</td>
                    <td class="text-center font-weight-bold global-taux-trimestre">{{ number_format($globalTauxTrimestre, 2) }}%</td>
                    <td class="text-center font-weight-bold global-total-annee">{{ $globalTotalPrevuAnnee }}</td>
                    <td class="text-center font-weight-bold global-nb-couverts-annee">{{ $globalTotalCouvertAnnee }}</td>
                    <td class="text-center font-weight-bold global-taux-annee">{{ number_format($globalTauxAnnee, 2) }}%</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // Fonction de debounce pour limiter la fréquence des appels AJAX
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // La fonction qui effectue la sauvegarde
    const saveHandler = debounce(function(inputElement) {
        const input = $(inputElement);
        const leconId = input.data('lecon-id');
        const nbCouverts = input.val();

        // Éviter d'envoyer des requêtes pour des valeurs vides ou invalides
        if (nbCouverts === '' || isNaN(parseInt(nbCouverts))) {
            return;
        }

        $.ajax({
            url: "{{ route('couverture.save') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                lecon_id: leconId,
                nb_couverts: nbCouverts,
                evaluation_id: {{ $evaluation->id }},
                classe_id: {{ $classe->id }}
            },
            success: function(response) {
                if (!response.success) {
                    console.error('La sauvegarde a échoué côté serveur.');
                    return;
                }

                const scId = input.closest('tr').data('sous-competence-id');

                // 1. Mettre à jour la ligne de la leçon
                const leconRow = $('tr[data-lecon-id="' + leconId + '"]');
                const leconStats = response.leconStats;
                leconRow.find('.taux-ua').text(parseFloat(leconStats.taux_ua).toFixed(2) + '%');
                leconRow.find('.nb-couverts-trimestre').text(leconStats.nb_couverts_trimestre);
                leconRow.find('.taux-trimestre').text(parseFloat(leconStats.taux_trimestre).toFixed(2) + '%');
                leconRow.find('.nb-couverts-annee').text(leconStats.nb_couverts_annee);
                leconRow.find('.taux-annee').text(parseFloat(leconStats.taux_annee).toFixed(2) + '%');

                // 2. Mettre à jour la ligne de total de la sous-compétence
                const scRow = $('tr.total-row[data-sc-id="' + scId + '"]');
                const scTotals = response.scTotals;
                scRow.find('.sc-nb-courant').text(scTotals.nb_courant);
                scRow.find('.sc-taux-ua').text(parseFloat(scTotals.taux_ua).toFixed(2) + '%');
                scRow.find('.sc-nb-couverts-trimestre').text(scTotals.nb_couverts_trimestre);
                scRow.find('.sc-taux-trimestre').text(parseFloat(scTotals.taux_trimestre).toFixed(2) + '%');
                scRow.find('.sc-nb-couverts-annee').text(scTotals.nb_couverts_annee);
                scRow.find('.sc-taux-annee').text(parseFloat(scTotals.taux_annee).toFixed(2) + '%');

                // 3. Mettre à jour la ligne de total global
                const globalRow = $('.total-global-row');
                const globalTotals = response.globalTotals;
                globalRow.find('.global-nb-courant').text(globalTotals.nb_courant);
                globalRow.find('.global-taux-ua').text(parseFloat(globalTotals.taux_ua).toFixed(2) + '%');
                globalRow.find('.global-nb-couverts-trimestre').text(globalTotals.nb_couverts_trimestre);
                globalRow.find('.global-taux-trimestre').text(parseFloat(globalTotals.taux_trimestre).toFixed(2) + '%');
                globalRow.find('.global-nb-couverts-annee').text(globalTotals.nb_couverts_annee);
                globalRow.find('.global-taux-annee').text(parseFloat(globalTotals.taux_annee).toFixed(2) + '%');
            },
            error: function(xhr) {
                console.error('Erreur AJAX:', xhr.status, xhr.responseText);
                alert('Une erreur critique est survenue. Vérifiez la console du navigateur.');
            }
        });
    }, 400); // Délai de 400ms

    // Attacher le gestionnaire d'événements
    $('body').on('input', '.nb-couverts-input', function() {
        saveHandler(this);
    });
});
</script>
</body>
</html>
