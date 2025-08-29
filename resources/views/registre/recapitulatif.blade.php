@extends('layouts.app')


@section('content')
<div class="container">
    <h2>Récapitulatif Couverture par Discipline</h2>
    <div class="mb-3">
        <a href="{{ route('registre.recapitulatif.pdf', ['session_id' => $session_id, 'classe_id' => $classe_id]) }}" class="btn btn-danger">Exporter en PDF</a>
        @if(empty($isPdf) || !$isPdf)
        @endif
    </div>
    <table class="table table-bordered table-striped" id="recap-table" data-trimestres='@json($trimestres)'>
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
    <td class="recap-trimestre-{{ $trim }}">{{ $row['trimestre'][$trim] ?? 0 }}</td>
@endforeach
<td class="recap-total-annuel">{{ $row['total_annuel'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script src="/js/recapitulatif.js"></script>
@endsection
