@php
// Ce template est quasi identique à show.blade.php, mais sans JS ni boutons

// Fonction pour convertir un nombre en format ordinal français
function rangOrdinal($nombre) {
    if ($nombre == 1) return '1er';
    if ($nombre == 2) return '2ème';
    if ($nombre == 3) return '3ème';
    if ($nombre == 4) return '4ème';
    if ($nombre == 5) return '5ème';
    if ($nombre == 6) return '6ème';
    if ($nombre == 7) return '7ème';
    if ($nombre == 8) return '8ème';
    if ($nombre == 9) return '9ème';
    if ($nombre == 10) return '10ème';
    return $nombre . 'ème';
}
@endphp
<table border="1" style="border-collapse:collapse;font-size:10px;width:100%">
    <thead>
        <tr>
            <th colspan="100%" style="font-size:16px;text-align:left;">SESSION {{ $session->nom ?? '' }} &nbsp;&nbsp; Evaluation numero: ______ &nbsp;&nbsp; mois de: ______</th>
        </tr>
        <tr>
            <th rowspan="3">#</th>
            <th rowspan="3">Matricule</th>
            <th rowspan="3">Nom & Prénom</th>
            @foreach($ordreCompetences as $comp)
                <th colspan="{{ array_sum(array_map(fn($sc) => count($sc['modalites'])+1, $comp['sous'])) }}">{{ e($comp['label']) }}</th>
            @endforeach
            <th rowspan="3">Total</th>
            <th rowspan="3">Moyenne</th>
            <th rowspan="3">Cote</th>
            <th rowspan="3">Rang</th>
        </tr>
        <tr>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    <th colspan="{{ count($sc['modalites'])+1 }}">{{ e($sc['label']) }}</th>
                @endforeach
            @endforeach
        </tr>
        <tr>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    @foreach($sc['modalites'] as $modalite)
                        <th>{{ e($modalite) }}</th>
                    @endforeach
                    <th>Total</th>
                @endforeach
            @endforeach
        </tr>
        <tr>
            <th></th><th></th><th></th>
            @foreach($ordreCompetences as $comp)
                @foreach($comp['sous'] as $sc)
                    @foreach($sc['modalites'] as $modalite)
                        <th>{{ e($pointsMaxMap[$sc['label']][$modalite] ?? '') }}</th>
                    @endforeach
                    <th>{{ e(array_sum($pointsMaxMap[$sc['label']] ?? [])) }}</th>
                @endforeach
            @endforeach
            <th></th><th></th><th></th><th></th>
        </tr>
    </thead>
    <tbody>
        @php
            // Calcul des moyennes pour tous les élèves
            $moyennes = [];
            foreach ($eleves as $el) {
                $grandTotal = 0;
                $totalPointsMax = 0;
                foreach($ordreCompetences as $comp) {
                    foreach($comp['sous'] as $sc) {
                        $scId = $sousCompetenceIds[$sc['label']] ?? null;
                        foreach($sc['modalites'] as $modaliteNom) {
                            $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                            $pointsMax = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                            $val = ($scId && $modaliteId && isset($el->notes_map[$scId]) && isset($el->notes_map[$scId][$modaliteId])) ? $el->notes_map[$scId][$modaliteId] : 0;
                            if (is_numeric($val)) $grandTotal += $val;
                            $totalPointsMax += $pointsMax;
                        }
                    }
                }
                $moyenne = $totalPointsMax > 0 ? ($grandTotal / $totalPointsMax) * 20 : 0;
                $moyennes[] = ['id' => $el->id, 'moyenne' => $moyenne];
            }
            // Trie décroissant des moyennes
            usort($moyennes, function($a, $b) { return $b['moyenne'] <=> $a['moyenne']; });
            $rangs = [];
            foreach ($moyennes as $i => $data) {
                if ($i > 0 && $data['moyenne'] == $moyennes[$i-1]['moyenne']) {
                    $rangs[$data['id']] = $rangs[$moyennes[$i-1]['id']];
                } else {
                    $rangs[$data['id']] = $i + 1;
                }
            }
        @endphp
        @foreach($eleves as $eleve)
<tr><td>{{ e($loop->iteration) }}</td><td>{{ e($eleve->matricule) }}</td><td>{{ e($eleve->nom) }} {{ e($eleve->prenom) }}</td>
                @php $grandTotalEleve = 0; @endphp
                @foreach($ordreCompetences as $comp)
                    @foreach($comp['sous'] as $sc)
                        @php
                            $scId = $sousCompetenceIds[$sc['label']] ?? null;
                            $totalSousComp = 0;
                        @endphp
                        @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                            @php
                                $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                                $val = '';
                                $pointsMax = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                                if ($scId && $modaliteId && isset($eleve->notes_map[$scId]) && isset($eleve->notes_map[$scId][$modaliteId])) {
                                    $val = $eleve->notes_map[$scId][$modaliteId];
                                    if (is_numeric($val)) { $totalSousComp += $val; }
                                }
                            @endphp
                            <td>{{ trim(e($val)) }}</td>
                        @endforeach
                        <td>{{ trim(e($totalSousComp)) }}</td>
                        @php $grandTotalEleve += $totalSousComp; @endphp
                    @endforeach
                @endforeach
                @php
                    // Calcul du total des points max pour l'élève (une seule fois, hors de la boucle d'élève)
                    $totalPointsMaxEleve = 0;
                    foreach($ordreCompetences as $comp) {
                        foreach($comp['sous'] as $sc) {
                            foreach($sc['modalites'] as $modaliteNom) {
                                $totalPointsMaxEleve += $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                            }
                        }
                    }
                @endphp
                }
                }
            }
        @endphp
        <td>{{ trim(e($grandTotalEleve)) }}</td>
        <td>@if($totalPointsMaxEleve > 0){{ trim(e(number_format(($grandTotalEleve / $totalPointsMaxEleve) * 20, 2))) }}@else - @endif</td>
        <td>@php $moyenne = $totalPointsMaxEleve > 0 ? ($grandTotalEleve / $totalPointsMaxEleve) * 20 : 0; @endphp
            @if((float)$moyenne >= 18)A+
            @elseif((float)$moyenne >= 16)A
            @elseif((float)$moyenne >= 14)B
            @elseif((float)$moyenne >= 12)C
            @elseif((float)$moyenne >= 10)D
            @else E
            @endif
        </td>
        <td>{{ rangOrdinal($rangs[$eleve->id] ?? 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
