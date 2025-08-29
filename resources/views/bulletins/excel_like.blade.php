<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin</title>
    <link href="{{ public_path('css/bulletin.css') }}" rel="stylesheet" />
</head>
<body>
@php
if(!function_exists('cote')){
    function cote($n){
        if(!is_numeric($n)) return '';
        return $n>=18?'A+':($n>=16?'A':($n>=14?'B+':($n>=12?'B':($n>=10?'C':'D'))));
    }
}
@endphp
@php($uaLabels=['UA1','UA2','UA3'])
@php($uaCols=['Notes','Cote'])
<div style="text-align:center; margin-bottom:4px;">
    <div class="small">REPUBLIQUE DU CAMEROUN – PAIX • TRAVAIL • PATRIE</div>
    <div class="small">MINISTERE DE L'EDUCATION DE BASE</div>
    <div class="small text-bold">ECOLE PUBLIQUE DE {{ $ecole??'_____'}} </div>
</div>
@foreach($classe->eleves as $eleve)
    <table class="table-bulletin" style="page-break-after:always;">
        <thead>
        <tr>
            <th rowspan="2" class="vertical-text" style="width:30px;">COMPÉTENCES</th>
            <th rowspan="2" style="width:140px;">SOUS COMPÉTENCES</th>
            <th colspan="{{ count($uaLabels)*count($uaCols)+1 }}">TRIMESTRE – 1er TRIMESTRE</th>
        </tr>
        <tr>
            <th class="bg-light" rowspan="1">UNITÉS<br>NOTES</th>
            @foreach($uaLabels as $lab)
                @foreach($uaCols as $c)
                    <th class="uacol">{{ $lab }}<br>{{ $c }}</th>
                @endforeach
            @endforeach
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @php($dataset=$dataParEleve[$eleve->id]['notes']??[])
        @php($totalEleve=0)
        @foreach($classe->niveau->competences as $comp)
            @php($compRowStart=true)
            @foreach($comp->sousCompetences as $sc)
                <tr>
                    @if($compRowStart)
                        <td class="vertical-text" rowspan="{{ $comp->sousCompetences->count()*1+1 }}">{{ strtoupper($comp->nom) }}</td>
                        @php($compRowStart=false)
                    @endif
                    <td style="text-align:left;">{{ $sc->nom }}</td>
                    <td></td>
                    @php($rowTotal=0)
                    @foreach($uaLabels as $idx=>$ua)
                        @php($ev=$evaluationMap[$ua]??($idx+1))
                        @php($firstModaliteId=optional($sc->modalites->first())->id)
                        @php($note=$firstModaliteId?($dataset[$sc->id][$firstModaliteId][$ev]??null):null)
                        <td>{{ $note!==null?$note:'' }}</td>
                        <td>{{ $note!==null?cote($note):'' }}</td>
                        @php($rowTotal+=$note??0)
                    @endforeach
                    <td>{{ $rowTotal }}</td>
                </tr>
                @php($totalEleve+=$rowTotal)
            @endforeach
            <tr class="bg-light text-bold"><td colspan="{{2+1+count($uaLabels)*count($uaCols)}}">Total {{ $comp->nom }}</td><td>{{ $totalEleve }}</td></tr>
        @endforeach
        </tbody>
    </table>
@endforeach
</body>
</html>
