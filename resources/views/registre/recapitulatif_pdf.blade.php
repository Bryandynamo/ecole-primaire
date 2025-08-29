<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif Couverture par Discipline</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        h2 {
            text-align: center;
            margin: 0 0 10px 0;
            font-size: 20px;
            font-weight: bold;
        }
        table { border-collapse: collapse; width: 100%; margin: 0 auto; }
        th, td { border: 1px solid #222; padding: 4px 6px; font-size: 12px; text-align: center; }
        th { background: #f1f1f1; font-weight: bold; }
       
    </style>
</head>
<body>
    <h2>
        Total des savoirs-faire par discipline<br>
        Classe : {{ $classe }}   Session : {{ $session }} <br>
      
    </h2>

    <table>
        <thead>
            <tr>
                <th>Sous-compétence</th>
                @foreach($uas as $ua)
                    <th>UA{{ $ua->numero_eval }}</th>
                @endforeach
                @foreach(array_keys($trimestres) as $trim)
                    <th>Trimestre {{ $trim }}</th>
                @endforeach
                <th>Total Annuel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recap as $row)
                <tr>
                    <td>{{ $row['sous_competence'] }}</td>
                    @foreach($uas as $ua)
                        <td>{{ $row['ua'][$ua->numero_eval] ?? 0 }}</td>
                    @endforeach
                    @foreach(array_keys($trimestres) as $trim)
                        <td>{{ $row['trimestre'][$trim] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $row['total_annuel'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
