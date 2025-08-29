<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Taux de couverture des programmes</title>
    <style>
        body {
    font-family: Arial, 'Liberation Sans', sans-serif;
    font-size: 10px;
    margin: 0;
    padding: 0;
}
        @page {
    size: A4 landscape;
    margin: 12mm;
}
        .pdf-container {
            margin: 0 auto;
            padding: 0;
            width: 100%;
        }
        h4 {
            text-align: center;
            font-weight: bold;
            font-size: 1.25em;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .infos {
            text-align: center;
            margin-bottom: 12px;
            font-size: 1em;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto 10px auto;
        }
        th, td {
    border: 1px solid #222;
    padding: 3px 5px;
    font-size: 9px;
    vertical-align: middle;
}
        th {
            background: #f1f1f1;
            font-weight: bold;
            white-space: nowrap;
        }
        .discipline {
            font-weight: bold;
            text-align: left;
            background: #fafafa;
        }
        .total-row {
            font-weight: bold;
            background: #e7e7e7;
        }
        td {
            background: #fff;
        }
        td[colspan] {
            text-align: right;
            font-weight: bold;
        }
        td.souscomp {
            font-weight: bold;
            text-align: left;
            background: #f8f8f8;
        }
        td.lecon {
            text-align: left;
            padding-left: 18px;
        }
        tr {
            page-break-inside: avoid;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            table {
                page-break-inside: avoid;
            }
            h4, .infos {
                margin-top: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <h4 style="text-align:center; margin: 0 0 4px 0; page-break-after: avoid;">TAUX DE COUVERTURE DES PROGRAMMES PAR DISCIPLINES ET PAR CLASSE</h4>
<div style="text-align:center; margin-bottom: 4px; page-break-after: avoid;">
    Année scolaire {{ $evaluation->session->nom ?? '' }}
    &nbsp;&nbsp; Trimestre {{ $evaluation->trimestre ?? '' }}
    &nbsp;&nbsp; Niveau {{ $classe->niveau->nom ?? '' }}
    &nbsp;&nbsp; Classe : {{ $classe->nom }}
</div>
<table style="page-break-inside: avoid;">
    <thead class="thead-light">
        <tr>
            <th rowspan="2" class="align-middle text-center">N°</th>
            <th rowspan="2" class="align-middle text-center">DISCIPLINES</th>
            <th rowspan="2" class="align-middle text-center">Prévu pour l’UA</th>
            <th colspan="3" class="text-center">Situation de l'UA</th>
            <th colspan="4" class="text-center">Situation par rapport au trimestre</th>
            <th colspan="3" class="text-center">Situation par rapport à l'année</th>
        </tr>
        <tr>
            <th class="text-center">Non couverts (UA préc.)</th>
            <th class="text-center">Couverts (fin UA)</th>
            <th class="text-center">Taux</th>
            <th class="text-center">Non couverts (Trim. préc.)</th>
            <th class="text-center">Total à couvrir (Trim.)</th>
            <th class="text-center">Couverts (fin Trim.)</th>
            <th class="text-center">Taux (Trim.)</th>
            <th class="text-center">Déjà couverts (Année)</th>
            <th class="text-center">Taux (Année)</th>
            <th class="text-center">Total Prévus (Année)</th>
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
            @php $num = 1; @endphp
            @foreach($scs as $sc)
                @php
                    $leconsPourSC = $lecons->get($sc->id, collect());
                    $rowspan = $leconsPourSC->count();
                    $firstRow = true;
                    $scTotals = [
                        'total_a_couvrir_ua' => 0,
                        'nb_non_couverts_precedent' => 0,
                        'nb_courant' => 0,
                        'taux_couverture_ua' => 0,
                        'total_trimestre' => 0,
                        'nb_couverts_trimestre' => 0,
                        'taux_trimestre' => 0,
                        'nb_deja_couverts' => 0,
                        'taux_annuel' => 0,
                        'total_annee' => 0,
                        'count_lecons' => 0
                    ];
                @endphp
                @if($rowspan > 0)
                    @foreach($leconsPourSC as $index => $lecon)
                        @if($lecon)
                            @php
                                $leconId = $lecon->id ?? null;
                                $stats = ($leconId && isset($leconStats[$leconId])) ? $leconStats[$leconId] : [
                                        'total_a_couvrir_ua' => 0,
                                        'nb_non_couverts_precedent' => 0,
                                        'nb_courant' => 0,
                                        'taux_couverture_ua' => 0,
                                        'total_trimestre' => 0,
                                        'nb_couverts_trimestre' => 0,
                                        'taux_trimestre' => 0,
                                        'nb_deja_couverts' => 0,
                                        'taux_annuel' => 0,
                                        'total_annee' => 0
                                    ];
                                $hasData = true;
                                $scTotals['total_a_couvrir_ua'] += $stats['total_a_couvrir_ua'];
                                $scTotals['nb_non_couverts_precedent'] += $stats['nb_non_couverts_precedent'];
                                $scTotals['nb_courant'] += $stats['nb_courant'];
                                $scTotals['taux_couverture_ua'] += $stats['taux_couverture_ua'];
                                $scTotals['total_trimestre'] += $stats['total_trimestre'];
                                $scTotals['nb_couverts_trimestre'] += $stats['nb_couverts_trimestre'];
                                $scTotals['taux_trimestre'] += $stats['taux_trimestre'];
                                $scTotals['nb_deja_couverts'] += $stats['nb_deja_couverts'];
                                $scTotals['taux_annuel'] += $stats['taux_annuel'];
                                $scTotals['total_annee'] += $stats['total_annee'];
                                $scTotals['count_lecons']++;
                            @endphp
                            <tr data-lecon-id="{{ $lecon->id }}" data-sous-competence-id="{{ $sc->id }}">
                                @if($firstRow)
                                    <td class="text-center align-middle" rowspan="{{ $rowspan }}">{{ $num }}</td>
                                    <td class="font-weight-bold align-middle" rowspan="{{ $rowspan }}">{{ $sc->nom }}</td>
                                    @php $firstRow = false; @endphp
                                @endif
                                <td class="text-center">{{ $lecon->nom }}</td>
                                <td class="text-center">{{ $stats['nb_non_couverts_precedent'] }}</td>
                                <td class="text-center">{{ $stats['nb_courant'] }}</td>
                                <td class="text-center taux-ua">{{ number_format($stats['taux_couverture_ua'], 2) }}%</td>
                                <td class="text-center">{{ $stats['nb_non_couverts_trim_prec'] ?? '' }}</td>
                                <td class="text-center">{{ $stats['total_trimestre'] }}</td>
                                <td class="text-center couverts-trim" data-lecon-id="{{ $lecon->id }}">{{ $stats['nb_couverts_trimestre'] }}</td>
                                <td class="text-center taux-trimestriel">{{ number_format($stats['taux_trimestre'], 2) }}%</td>
                                <td class="text-center">{{ $stats['nb_deja_couverts'] }}</td>
                                <td class="text-center taux-annuel">{{ number_format($stats['taux_annuel'], 2) }}%</td>
                                <td class="text-center">{{ $stats['total_annee'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="table-warning total-row">
                        <td colspan="3" class="text-right fw-bold">Total</td>
                        <td class="text-center total-nb-non-couverts" data-sc-id="{{ $sc->id }}">{{ $scTotals['nb_non_couverts_precedent'] }}</td>
                        <td class="text-center total-nb-courant" data-sc-id="{{ $sc->id }}">{{ $scTotals['nb_courant'] }}</td>
                        <td class="text-center total-taux-ua" data-sc-id="{{ $sc->id }}">{{ $scTotals['taux_couverture_ua'] }}</td>
                        <td class="text-center">{{ $scTotals['nb_non_couverts_trim_prec'] ?? '' }}</td>
                        <td class="text-center">{{ $scTotals['total_trimestre'] }}</td>
                        <td class="text-center">{{ $scTotals['nb_couverts_trimestre'] }}</td>
                        <td class="text-center">{{ $scTotals['taux_trimestre'] }}</td>
                        <td class="text-center">{{ $scTotals['nb_deja_couverts'] }}</td>
                        <td class="text-center">{{ $scTotals['taux_annuel'] }}</td>
                        <td class="text-center">{{ $scTotals['total_annee'] }}</td>
                    </tr>
                    @php $num++; @endphp
                @endif
            @endforeach
        @empty
            <tr>
                <td colspan="13" class="text-center">Aucune donnée disponible pour cette classe et cette évaluation.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
</body>
</html>
