<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Couverture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Styles pour les bordures du tableau */
        table, th, td {
            border: 1px solid black !important;
            border-collapse: collapse !important;
        }
        /* Styles pour les lignes de total */
        .table-warning {
            background-color: #fff3cd !important; /* Couleur jaune Bootstrap pour table-warning */
        }
        /* Styles pour l'en-tête du tableau */
        .table-secondary {
            background-color: #e2e3e5 !important; /* Couleur grise Bootstrap pour table-secondary */
        }
    </style>
</head>
<body>
<div class="container-fluid">
    @if(!isset($is_pdf) || !$is_pdf)
        <a href="{{ route('registre.index') }}" class="btn btn-secondary mb-3">Retour</a>
    @endif
    <div class="row mb-3">
        @if(!isset($is_pdf) || !$is_pdf)
        <div class="col-12 text-right mb-2">
            <div class="btn-group">
                <a href="{{ route('couverture.exportPdf', ['classe' => $classe->id, 'evaluation' => $evaluation->id]) }}" 
                   class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </a>
                <a href="{{ route('couverture.exportExcel', ['classe' => $classe->id, 'evaluation' => $evaluation->id]) }}" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Exporter Excel
                </a>
            </div>
        </div>
        @endif
        <div class="col-12">
            <div style="text-align:center; margin-bottom: 0.5em;">
                <h4 class="font-weight-bold mb-2" style="margin-bottom: 0.2em;">TAUX DE COUVERTURE</h4>
                <div style="font-size:1.1em;">
                    <strong>Session :</strong> {{ date('Y') }}-{{ date('Y') + 1 }}
                    &nbsp;&nbsp;|
                    <strong>Trimestre :</strong> {{ $evaluation->trimestre ?? 'N/A' }}
                    &nbsp;&nbsp;|
                    <strong>UA:</strong> {{ $evaluation->numero_eval ?? $evaluation->nom }}
                </div>
                <div style="font-size:1.1em;">
                    <strong>Niveau :</strong> {{ $classe->niveau->nom ?? '' }}
                    &nbsp;&nbsp;|
                    <strong>Classe :</strong> {{ $classe->nom }}
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="table-secondary">
            <tr>
                <th rowspan="2" class="text-center">N°</th>
                <th rowspan="2" class="text-center">Discpline</th>
                <th rowspan="2" class="text-center">Lecons</th>
                <th colspan="7" class="text-center">Couverture mensuelle des savoirs</th>
                <th colspan="3" class="text-center">Situation par rapport à l'année</th>
            </tr>
            <tr>
                <th class="text-center ">A couvrir pour l'UA</th>
                <th class="text-center ">Non couverts<br>(Précédent)</th>
                <th class="text-center ">Nombre couvert pour l'ua</th>
                <th class="text-center t">Taux de couverture pour l'UA </th>

                <th class="text-center">A couvrir pour Trimestre</th>
                <th class="text-center">Nombre Couverts<br>(Trimestre)</th>
                <th class="text-center">Taux de couverture<br>(Trimestre)</th>
                <th class="text-center">A couvrir pour l' Année</th>
                <th class="text-center">Deja couvert <br>(Année)</th>
                <th class="text-center">Taux de couverture<br>(Annuel)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $num = 1;
                $hasData = false;
            @endphp

            @forelse($sousCompetences as $sc)
                
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
                        


                        
                        {{-- Lignes des leçons --}}
                        @php
                            $leconsCount = $leconsPourSC->count();
                        @endphp
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
                                @if($loop->first)
                                    <td class="text-center" rowspan="{{ $leconsCount }}">{{ $num }}</td>
                                @endif
                                @if($loop->first)
                                    <td rowspan="{{ $leconsCount }}">{{ $sc->nom }}</td>
                                @endif
                                <td>{{ $lecon->nom }}</td>
                                <td class="text-center">{{ $stats['total_a_couvrir_ua'] }} <!-- Debug: UA={{ $evaluation->numero_eval }} --></td>
                                <td class="text-center">{{ $stats['nb_non_couverts_precedent'] }}</td>
                                <td class="text-center">
@if (isset($is_pdf) && $is_pdf)
    {{ $stats['nb_courant'] }}
@else
    <input type="number" 
           class="form-control form-control-sm nb-couverts-input text-center" 
           data-lecon-id="{{ $lecon->id }}" 
           value="{{ $stats['nb_courant'] }}" 
           min="0" 
           max="{{ $stats['total_a_couvrir_ua'] }}"
           title="Maximum: {{ $stats['total_a_couvrir_ua'] }} heures"
           style="width: 70px;">
@endif
                                </td>
                                <td class="text-center taux-ua">{{ number_format($stats['taux_ua'], 2) }}%</td>
                                <td class="text-center">{{ $stats['total_trimestre'] }}</td>
                                <td class="text-center nb-couverts-trimestre">{{ $stats['nb_couverts_trimestre'] ?? 0 }}</td>
                                <td class="text-center taux-trimestre">{{ number_format($stats['taux_trimestre'], 2) }}%</td>
                                <td class="text-center">{{ $stats['total_annee'] }}</td>
                                <td class="text-center nb-couverts-annee">{{ $stats['nb_couverts_annee'] ?? 0 }}</td>
                                <td class="text-center taux-annee">{{ number_format($stats['taux_annee'], 2) }}%</td>
                            </tr>
                        @endforeach
                        
                        {{-- Ligne de total pour la sous-compétence --}}
                        <tr class="table-warning total-row" data-sc-id="{{ $sc->id }}">
                            <td colspan="3" class="text-right font-weight-bold">Total</td>
                            <td class="text-center font-weight-bold sc-total-prevu-ua">{{ $scTotals['total_prevu_ua'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-nb-non-couverts-precedent">{{ $scTotals['nb_non_couverts_precedent'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-nb-courant">{{ $scTotals['nb_courant'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-taux-ua">{{ number_format($scTotals['taux_ua'] ?? 0, 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-trimestre">{{ $scTotals['total_trimestre'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-trimestre">{{ $scTotals['nb_couverts_trimestre'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-taux-trimestre">{{ number_format($scTotals['taux_trimestre'] ?? 0, 2) }}%</td>
                            <td class="text-center font-weight-bold sc-total-annee">{{ $scTotals['total_annee'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-nb-couverts-annee">{{ $scTotals['nb_couverts_annee'] ?? 0 }}</td>
                            <td class="text-center font-weight-bold sc-taux-annee">{{ number_format($scTotals['taux_annee'] ?? 0, 2) }}%</td>
                        </tr>
                        
                        @php $num++; @endphp
                    @endif
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
                    <td class="text-center font-weight-bold global-taux-ua">{{ number_format(min(100, $globalTauxUa), 2) }}%</td>
                    <td class="text-center font-weight-bold global-total-trimestre">{{ $globalTotalPrevuTrimestre }}</td>
                    <td class="text-center font-weight-bold global-nb-couverts-trimestre">{{ $globalTotalCouvertTrimestre }}</td>
                    <td class="text-center font-weight-bold global-taux-trimestre">{{ number_format(min(100, $globalTauxTrimestre), 2) }}%</td>
                    <td class="text-center font-weight-bold global-total-annee">{{ $globalTotalPrevuAnnee }}</td>
                    <td class="text-center font-weight-bold global-nb-couverts-annee">{{ $globalTotalCouvertAnnee }}</td>
                    <td class="text-center font-weight-bold global-taux-annee">{{ number_format(min(100, $globalTauxAnnee), 2) }}%</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // Fonction de debounce pour limiter la fréquence des appels AJAX
    function logState() {
        // Log le contenu d'une cellule de taux global toutes les 2s
        setInterval(function() {
            const val = $('.global-taux-ua').text();
            console.log('[DEBUG] Taux global UA visible:', val);
        }, 2000);
    }

    logState();

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
        const nbCouverts = parseInt(input.val());
        const maxValue = parseInt(input.attr('max'));

        // Éviter d'envoyer des requêtes pour des valeurs vides ou invalides
        if (input.val() === '' || isNaN(nbCouverts)) {
            return;
        }

        // Vérifier si la valeur dépasse le maximum autorisé
        if (nbCouverts > maxValue) {
            alert(`Erreur: Vous ne pouvez pas saisir plus de ${maxValue} heures pour cette leçon.`);
            input.val(maxValue); // Remettre la valeur au maximum autorisé
            input.focus();
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
                    alert(response.message || 'Erreur côté serveur.');
                    return;
                }
                console.log('[DEBUG] Réponse AJAX succès, mise à jour DOM pour leçon', leconId);

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
        console.log('[DEBUG] Input modifié pour leçon', $(this).data('lecon-id'), 'valeur', $(this).val());
        saveHandler(this);
    });
});
</script>
</body>
</html>
