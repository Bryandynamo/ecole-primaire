
<!-- ATTENTION : NE PAS réactiver ni importer /js/couverture.js ni de script de recalcul automatique !
Tout script de recalcul (updateAll, updateTotalRow, updateLessonRow) annule l'affichage dynamique AJAX des totaux et fait DISPARAÎTRE les valeurs. Laisse UNIQUEMENT la logique AJAX en place. -->
<pre style="font-size:11px;max-height:350px;overflow:auto;background:#f9f9f9;border:1px solid #ccc;">
<strong>
$leconStats :
</strong>
{{ json_encode($leconStats, JSON_PRETTY_PRINT) }}
<strong>
$lecons :
</strong>
{{ json_encode($lecons, JSON_PRETTY_PRINT) }}
<strong>
$sousCompetences :
</strong>
{{ json_encode($sousCompetences, JSON_PRETTY_PRINT) }}
</pre>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 text-right mb-2">
            <a href="{{ route('couverture.exportPdf', ['classe' => $classe->id, 'evaluation' => $evaluation->id]) }}" class="btn btn-danger btn-sm" target="_blank">
                Exporter PDF
            </a>
        </div>
        <div class="col text-center">
            <h4 class="font-weight-bold">TAUX DE COUVERTURE DES PROGRAMMES PAR DISCIPLINES ET PAR CLASSE</h4>
            <div>
                Année scolaire {{ $evaluation->session->nom ?? '' }}
&nbsp;&nbsp; Trimestre {{ $evaluation->trimestre ?? '' }}
&nbsp;&nbsp; <strong>UA : {{ $evaluation->numero ?? $evaluation->id }}</strong>
&nbsp;&nbsp; Niveau {{ $classe->niveau->nom ?? '' }}
&nbsp;&nbsp; Classe : {{ $classe->nom }}
            </div>
        </div>
    </div>
    <form id="couverture-form">
<table class="table table-bordered table-sm align-middle" style="font-size: 14px; min-width: 1100px;">
        <thead class="thead-light">
    <tr>
        <th rowspan="2" class="align-middle text-center">Sous-Compétence</th>
        <th rowspan="2" class="align-middle text-center">Prévu pour l'UA</th>
        <th colspan="3" class="text-center bg-light">Situation de l'UA ({{ $evaluation->name ?? 'UA' }})</th>
        <th colspan="3" class="text-center">Situation par rapport au trimestre</th>
        <th colspan="3" class="text-center">Situation par rapport à l'année</th>
    </tr>
    <tr>
        {{-- Colonnes pour l'UA --}}
        <th class="text-center bg-light">Non couverts<br>(Début UA)</th>
        <th class="text-center bg-light">Nb. Courant</th>
        <th class="text-center bg-light">Taux Couv.</th>
        
        {{-- Colonnes pour le Trimestre --}}
        <th class="text-center">Total Trimestre</th>
        <th class="text-center">Nb. Couverts<br>(Trimestre)</th>
        <th class="text-center">Taux<br>(Trimestre)</th>

        {{-- Colonnes pour l'Année --}}
        <th class="text-center">Nb. Déjà Couverts<br>(Année)</th>
        <th class="text-center">Taux<br>(Annuel)</th>
        <th class="text-center">Total Année</th>
    </tr>
</thead>
        <tbody>
            @php 
                $num = 1;
                $hasData = false;
            @endphp
            

            @php
                // Grouper les sous-compétences par discipline
                $sousCompetencesParDiscipline = $sousCompetences->groupBy(function($sc) {
                    return $sc->competence->nom ?? 'Autre';
                });
                $num = 1;
            @endphp

            @forelse($sousCompetencesParDiscipline as $discipline => $scs)
                @php
                    // On prépare les totaux pour la discipline
                    $disciplineTotals = [
                        'total_prevu_ua' => 0,
                        'total_courant_ua' => 0,
                        'nb_non_couverts_precedent' => 0,
                        'nb_courant' => 0,
                        'taux_couverture_ua' => 0,
                        'total_trimestre' => 0,
                        'nb_couverts_trimestre' => 0,
                        'taux_trimestre' => 0,
                        'nb_deja_couverts' => 0,
                        'taux_annuel' => 0,
                        'total_annee' => 0,
                        'nb_couverts_annee' => 0, 
                        'count_lecons' => 0
                    ];
                @endphp
                
                @foreach($scs as $sc)
                    @php
                        $leconsPourSC = $lecons->get($sc->id, collect());
                        $rowspan = $leconsPourSC->count();
                        $firstRow = true;
                        // Préparer les totaux pour cette sous-compétence
                        $scTotals = [
                            'total_prevu_ua' => 0,
                            'nb_non_couverts_precedent' => 0,
                            'nb_courant' => 0,
                            'total_trimestre' => 0,
                            'nb_couverts_trimestre' => 0,
                            'taux_trimestre' => 0,
                            'nb_deja_couverts' => 0,
                            'taux_annuel' => 0,
                            'total_annee' => 0,
                            'nb_couverts_annee' => 0,
                            'count_lecons' => 0
                        ];
                    @endphp
                    @if($rowspan > 0)
                        @foreach($leconsPourSC as $index => $lecon)
                            @php
                                $leconId = $lecon->id;
                                $stats = $leconStats[$leconId] ?? [
                                    'total_a_couvrir_ua' => 0, 'nb_non_couverts_precedent' => 0, 'nb_courant' => 0, 'taux_ua' => 0,
                                    'total_trimestre' => 0, 'nb_couverts_trimestre' => 0, 'taux_trimestre' => 0,
                                    'total_annee' => 0, 'nb_couverts_annee' => 0, 'taux_annee' => 0,
                                ];
                                $hasData = true;

                                // La clé 'total_a_couvrir_ua' est déjà la bonne grâce au contrôleur
                                $total_a_couvrir_ua = $stats['total_a_couvrir_ua'] ?? 0;

                                // Accumuler les totaux pour la sous-compétence
                                $scTotals['total_prevu_ua'] += $total_a_couvrir_ua;
                                $scTotals['nb_non_couverts_precedent'] += $stats['nb_non_couverts_precedent'] ?? 0;
                                $scTotals['nb_courant'] += $stats['nb_courant'] ?? 0;
                                $scTotals['total_trimestre'] += $stats['total_trimestre'] ?? 0;
                                $scTotals['nb_couverts_trimestre'] += $stats['nb_couverts_trimestre'] ?? 0;
                                $scTotals['total_annee'] += $stats['total_annee'] ?? 0;
                                $scTotals['nb_couverts_annee'] += $stats['nb_couverts_annee'] ?? 0;
                                $scTotals['count_lecons']++;
                            @endphp
                            <tr data-lecon-id="{{ $lecon->id }}" data-sous-competence-id="{{ $sc->id }}">
                                @if($firstRow)
                                    <td class="font-weight-bold align-middle" rowspan="{{ $rowspan }}">{{ $sc->nom }}</td>
                                @endif
                                <td class="text-left">{{ $lecon->nom }}</td>
                                <td class="text-center">{{ $total_a_couvrir_ua }}</td>
                                <td class="text-center">{{ $stats['nb_non_couverts_precedent'] ?? 0 }}</td>
                                <td class="text-center">
                                    <input type="number" class="form-control form-control-sm nb-couverts-input" value="{{ $stats['nb_courant'] ?? 0 }}" data-lecon-id="{{ $lecon->id }}" min="0">
                                </td>
                                <td class="text-center taux-ua">{{ number_format($stats['taux_ua'] ?? 0, 2) }}%</td>
                                <td class="text-center total-trimestre">{{ $stats['total_trimestre'] ?? 0 }}</td>
                                <td class="text-center nb-couverts-trimestre">{{ $stats['nb_couverts_trimestre'] ?? 0 }}</td>
                                <td class="text-center taux-trimestre">{{ number_format($stats['taux_trimestre'] ?? 0, 2) }}%</td>
                                <td class="text-center">{{ $stats['nb_couverts_annee'] ?? 0 }}</td>
                                <td class="text-center taux-annee">{{ number_format($stats['taux_annee'] ?? 0, 2) }}%</td>
                                <td class="text-center total-annee">{{ $stats['total_annee'] ?? 0 }}</td>
                            </tr>
                            @php $firstRow = false; @endphp
                        @endforeach

                        @php
                            // Calcul des taux pour la sous-compétence
                            $sc_taux_ua = ($scTotals['total_prevu_ua'] > 0) ? ($scTotals['nb_courant'] / $scTotals['total_prevu_ua']) * 100 : 0;
                            $sc_taux_trimestre = ($scTotals['total_trimestre'] > 0) ? ($scTotals['nb_couverts_trimestre'] / $scTotals['total_trimestre']) * 100 : 0;
                            $sc_taux_annee = ($scTotals['total_annee'] > 0) ? ($scTotals['nb_couverts_annee'] / $scTotals['total_annee']) * 100 : 0;

                            // Ajouter les totaux de la sous-compétence aux totaux de la discipline
                            $disciplineTotals['total_prevu_ua'] += $scTotals['total_prevu_ua'];
                            $disciplineTotals['total_courant_ua'] += $scTotals['nb_courant'];
                            $disciplineTotals['total_trimestre'] += $scTotals['total_trimestre'];
                            $disciplineTotals['nb_couverts_trimestre'] += $scTotals['nb_couverts_trimestre'];
                            $disciplineTotals['total_annee'] += $scTotals['total_annee'];
                            $disciplineTotals['nb_couverts_annee'] += $scTotals['nb_couverts_annee'];
                        @endphp
                        <tr class="table-warning total-row font-weight-bold" data-sc-id="{{ $sc->id }}">
                            <td colspan="2" class="text-right">TOTAL {{ $sc->nom }}</td>
                            <td class="text-center sc-total-prevu-ua">{{ $scTotals['total_prevu_ua'] }}</td>
                            <td class="text-center sc-nb-non-couverts-precedent">{{ $scTotals['nb_non_couverts_precedent'] }}</td>
                            <td class="text-center sc-nb-courant">{{ $scTotals['nb_courant'] }}</td>
                            <td class="text-center sc-taux-ua">{{ number_format($sc_taux_ua, 2) }}%</td>
                            <td class="text-center sc-total-trimestre">{{ $scTotals['total_trimestre'] }}</td>
                            <td class="text-center sc-nb-couverts-trimestre">{{ $scTotals['nb_couverts_trimestre'] }}</td>
                            <td class="text-center sc-taux-trimestre">{{ number_format($sc_taux_trimestre, 2) }}%</td>
                            <td class="text-center sc-nb-couverts-annee">{{ $scTotals['nb_couverts_annee'] }}</td>
                            <td class="text-center sc-taux-annee">{{ number_format($sc_taux_annee, 2) }}%</td>
                            <td class="text-center sc-total-annee">{{ $scTotals['total_annee'] }}</td>
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
});
</script>
</form>
</div>

<!-- Bloc scripts désactivé pour empêcher toute réinitialisation parasite du DOM -->
<!--
@push('scripts')
<div id="classe-id" data-classe-id="{{ $classe->id }}" style="display: none;"></div>
<div id="evaluation-id" data-evaluation-id="{{ $evaluation->id }}" style="display: none;"></div>
<div id="save-url" data-url="{{ route('couverture.save') }}" style="display: none;"></div>
<script>
if (typeof jQuery === 'undefined') {
    console.error('jQuery n\'est pas chargé');
    alert('Erreur: jQuery n\'est pas chargé');
} else {
    console.log('jQuery version:', jQuery.fn.jquery);
    console.log('Test jQuery:', $('body').length);
}
</script>
<script src="/js/couverture.js"></script>
<script>
$(document).ready(function() {
    // Initialisation des calculs au chargement de la page
    // updateAll();
});
</script>
@endpush
-->
</body>
</html>
