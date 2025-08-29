<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Synthèse des compétences</title>
    <style>
        /* Règle pour contrôler les marges de la page PDF */
        @page {
            size: A4 landscape;
            margin: 0.5cm; /* Marges minimales sur tous les côtés pour maximiser l'espace */
        }

        body { 
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px; 
            /* Centrage vertical et horizontal du contenu */
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 98%;
        }
        table {
            border-collapse: collapse; 
            width: 100%; 
            table-layout: fixed; /* Force le respect des largeurs de colonnes définies */
        }
        th, td {
            border: 1px solid #222; 
            padding: 7px; 
            text-align: center; 
            font-size: 11px; 
            word-wrap: break-word; 
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .header-info {
            text-align: center;
            margin-bottom: 10px;
        }
        h4 {
            font-size: 14px;
            margin: 0;
        }
        .small-text {
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="main-container">
    <div class="header-info">
        <h4>Synthèse des compétences par compétence</h4>
        <div class="small-text">
            <b>Classe :</b> {{ $classe->nom }} | 
            <b>Évaluation :</b> {{ $evaluation->libelle ?? $evaluation->id }} | 
            <b>Année scolaire :</b> {{ $session->nom }}
        </div>
    </div>

    @if(empty($synthese) || count($synthese) === 0)
        <div style="text-align: center; padding: 20px; border: 1px solid #ccc;">Aucune donnée de synthèse disponible pour cette évaluation.</div>
    @else
        <table style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 14%; text-align: left; padding-left: 5px;">Compétence</th>
                    <th colspan="3">INSCRITS</th>
                    <th colspan="3">PRESENT</th>
                    <th colspan="6">EXPERT</th>
                    <th colspan="6">ACQUIS</th>
                    <th colspan="6">EN COURS D'ACQUISITION</th>
                    <th colspan="6">NON ACQUIS</th>
                </tr>
                <tr>
                    <th>G</th><th>F</th><th>T</th>
                    <th>G</th><th>F</th><th>T</th>
                    <th>G</th><th>F</th><th>T</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%G</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%F</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%T</th>
                    <th>G</th><th>F</th><th>T</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%G</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%F</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%T</th>
                    <th>G</th><th>F</th><th>T</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%G</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%F</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%T</th>
                    <th>G</th><th>F</th><th>T</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%G</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%F</th><th style="width:120px; min-width:120px; max-width:120px; text-align:center; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">%T</th>
                </tr>
            </thead>
            <tbody>
                @foreach($synthese as $row)
                <tr>
                    <td style="text-align: left; padding-left: 5px;">{{ $row['sous_competence'] }}</td>
                    <td>{{ $row['inscrits_g'] }}</td>
                    <td>{{ $row['inscrits_f'] }}</td>
                    <td>{{ $row['inscrits_t'] }}</td>
                    <td>{{ $row['present_g'] }}</td>
                    <td>{{ $row['present_f'] }}</td>
                    <td>{{ $row['present_t'] }}</td>
                    <td>{{ $row['experts_g'] }}</td>
                    <td>{{ $row['experts_f'] }}</td>
                    <td>{{ $row['experts_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['experts_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['experts_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['experts_t_p'], 1) }}</td>
                    <td>{{ $row['acquis_g'] }}</td>
                    <td>{{ $row['acquis_f'] }}</td>
                    <td>{{ $row['acquis_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['acquis_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['acquis_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['acquis_t_p'], 1) }}</td>
                    <td>{{ $row['encours_g'] }}</td>
                    <td>{{ $row['encours_f'] }}</td>
                    <td>{{ $row['encours_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['encours_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['encours_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['encours_t_p'], 1) }}</td>
                    <td>{{ $row['nonacquis_g'] }}</td>
                    <td>{{ $row['nonacquis_f'] }}</td>
                    <td>{{ $row['nonacquis_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['nonacquis_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['nonacquis_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222;">{{ number_format($row['nonacquis_t_p'], 1) }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background: #eaeaea;">
                    <td style="text-align: left; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">TOTAL</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['inscrits_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['inscrits_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['inscrits_t'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['present_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['present_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['present_t'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['experts_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['experts_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['experts_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['experts_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['experts_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['experts_t_p'], 1) }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['acquis_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['acquis_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['acquis_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['acquis_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['acquis_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['acquis_t_p'], 1) }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['encours_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['encours_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['encours_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['encours_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['encours_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['encours_t_p'], 1) }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['nonacquis_g'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['nonacquis_f'] }}</td>
                    <td style="text-align: center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ $totaux['nonacquis_t'] }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['nonacquis_g_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['nonacquis_f_p'], 1) }}</td>
                    <td style="width:120px; min-width:120px; max-width:120px; white-space:nowrap; text-align:center; padding:4px 8px; font-size:11px; font-family:Arial,Helvetica,sans-serif; border:1px solid #222; background: #eaeaea;">{{ number_format($totaux['nonacquis_t_p'], 1) }}</td>
                </tr>
            </tbody>
        </table>
    @endif
    </div>
</body>
</html>
