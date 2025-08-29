<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif de la Couverture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Styles spécifiques pour le PDF, conditionnés par la variable $is_pdf */
    @if(isset($is_pdf) && $is_pdf)
    body {
        font-family: 'DejaVu Sans', sans-serif;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th, td {
        border: 1px solid #666; /* Bordures plus fines et grises */
        padding: 4px;
        text-align: center;
        font-size: 9px; /* Police légèrement réduite pour l'équilibre */
        word-wrap: break-word;
        vertical-align: middle; /* Centrage vertical parfait */
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    th.col-numero { width: 3%; }
    th.col-discipline { width: 15%; }
    th.col-lecon { width: 22%; }
    th.col-ua { width: 4%; }
    th.col-total { width: 5%; }
    th.col-annuel { width: 6%; }
    .discipline-nom, .lecon-nom { text-align: left; padding-left: 5px; }

    .no-export {
        display: none !important;
    }

    thead {
        display: table-header-group; /* Répéter l'en-tête sur chaque page */
    }
    tbody {
        page-break-inside: avoid !important; /* La règle CLÉ : ne jamais couper un bloc discipline */
    }
    @endif
</style>
</head>
<body>
<div class="container-fluid">
    @if(!isset($is_pdf) || !$is_pdf)
        <a href="{{ route('registre.index') }}" class="btn btn-secondary mb-3">Retour</a>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-center">Récapitulatif de la couverture – Session : {{ $session->nom }} – Classe : {{ $classe->nom }}</h1>
            <div class="btn-group">
                <a href="{{ route('couverture.recap.pdf', ['classe' => $classe->id]) }}" class="btn btn-primary">Exporter en PDF</a>
                <a href="{{ route('couverture.recap.excel', ['classe' => $classe->id]) }}" class="btn btn-success">Exporter en Excel</a>
            </div>
        </div>
    @else
        <h3 class="text-center font-weight-bold mb-3">Récapitulatif de la couverture – Session : {{ $session->nom }} – Classe : {{ $classe->nom }}</h3>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="col-numero">N°</th>
                    <th class="col-discipline">Discipline (Sous-compétence)</th>
                    <th class="col-lecon">Leçons</th>
                    @foreach($uasByTrimestre as $trimestre => $uas)
                        @foreach($uas as $ua)
                            <th class="col-ua">UA{{ $ua->numero_eval }}</th>
                        @endforeach
                        <th class="col-total">Total T{{$trimestre}}</th>
                    @endforeach
                    <th class="col-annuel">Total Annuel</th>
                </tr>
            </thead>

            @php $counter = 1; @endphp
            @foreach($recapData as $scId => $data)
                @if(count($data['lecons']) > 0)
                <tbody>
                    @foreach($data['lecons'] as $index => $lecon)
                        <tr>
                            @if($index === 0)
                                <td rowspan="{{ count($data['lecons']) }}">{{ $counter++ }}</td>
                                <td rowspan="{{ count($data['lecons']) }}" class="discipline-nom">{{ $data['nom'] }}</td>
                            @endif
                            <td class="lecon-nom">{{ $lecon['nom'] }}</td>
                            @foreach($uasByTrimestre as $trimestre => $uas)
                                @foreach($uas as $ua)
                                    <td>{{ $lecon['totaux']['ua_' . $ua->numero_eval] ?? 0 }}</td>
                                @endforeach
                                <td class="fw-bold">{{ $lecon['totaux']['trimestre_' . $trimestre] ?? 0 }}</td>
                            @endforeach
                            <td class="fw-bold">{{ $lecon['totaux']['annuel'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @endif
            @endforeach
        </table>
    </div>
</div>

<script>
// La logique AJAX pour récupérer et mettre à jour les données sera ajoutée ici plus tard.
</script>

</body>
</html>
