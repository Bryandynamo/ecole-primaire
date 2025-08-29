@php
if (!function_exists('cote')) {
    function cote($note) {
        if (!is_numeric($note)) return '';
        if ($note >= 18) return 'A+';
        if ($note >= 16) return 'A';
        if ($note >= 14) return 'B+';
        if ($note >= 12) return 'B';
        if ($note >= 10) return 'C';
        return 'D';
    }
}

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

@foreach($classe->eleves as $eleve)
    <div style="page-break-after:always;font-size:12px;">
        <h3 style="text-align:center;">BULLETIN DE NOTES – {{ implode(',', $evalIds) }} {{ count($evalIds)==3 ? ' (Trimestre)' : '(Évaluation '.$evalIds[0].')' }}</h3>
        <p><b>École :</b> {{ config('app.ecole_nom','Ecole Publique') }}<br>
           <b>Session :</b> {{ $session->nom }}<br>
           <b>Classe :</b> {{ $classe->nom }}<br>
           <b>Élève :</b> {{ $eleve->nom }} {{ $eleve->prenom }}
        </p>
        <table border="1" width="100%" cellpadding="3" cellspacing="0">
            <thead>
                <tr>
                    <th>Compétence</th>
                    <th>Sous-compétence</th>
@foreach($evalIds as $ev)
                    <th>UA{{ $ev }}<br>Note</th>
                    <th>UA{{ $ev }}<br>Cote</th>
@endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
@php
$dataset = $dataParEleve[$eleve->id]['notes'] ?? [];
$totalEleve = 0;
@endphp
@foreach($classe->niveau->competences as $comp)
    @foreach($comp->sousCompetences as $sc)
        @php $rowTotal = 0; @endphp
        <tr>
            <td>{{ $comp->nom }}</td>
            <td>{{ $sc->nom }}</td>
            @foreach($evalIds as $ev)
                @php
                    $firstModaliteId = optional($sc->modalites->first())->id;
                    $note = $firstModaliteId ? ($dataset[$sc->id][$firstModaliteId][$ev] ?? null) : null;
                @endphp
                <td>{{ $note !== null ? $note : '—' }}</td>
                <td>{{ $note !== null ? cote($note) : '—' }}</td>
                @php $rowTotal += $note ?? 0; @endphp
            @endforeach
            <td>{{ $rowTotal }}</td>
        </tr>
        @php $totalEleve += $rowTotal; @endphp
    @endforeach
@endforeach
            </tbody>
        </table>
        <p><b>Total points :</b> {{ $totalEleve }} | <b>Moyenne :</b> {{ $dataParEleve[$eleve->id]['moyenne'] ?? '—' }} <span style="color:#007bff; font-weight:bold;">({{ rangOrdinal($dataParEleve[$eleve->id]['rang'] ?? 0) }})</span></p>
        
        @if(count($evalIds) > 1)
        <table border="1" width="100%" cellpadding="3" cellspacing="0" style="margin-top:10px;">
            <thead>
                <tr>
                    <th colspan="{{ count($evalIds) + 1 }}" style="background:#f8f9fa; text-align:center;">Rangs par UA</th>
                </tr>
                <tr>
                    <th>UA</th>
                    @foreach($evalIds as $ev)
                        <th>UA{{ $ev }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Rang</b></td>
                    @foreach($evalIds as $ev)
                        <td style="text-align:center;">{{ rangOrdinal($dataParEleve[$eleve->id]['rangsUA'][$ev] ?? 0) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        @endif
        
        <p>Visa parent : ____________ &nbsp;&nbsp; Visa enseignant : ____________ &nbsp;&nbsp; Visa directeur : ____________</p>
    </div>
@endforeach
