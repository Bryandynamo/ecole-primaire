<style>
    @page { size: A4 landscape; margin: 0.7cm; }
    body { font-size: 10px; }
    .registre-table, .registre-table th, .registre-table td {
        border: 1px solid #000;
        border-collapse: collapse;
        font-size: 9px;
        word-break: break-word;
    }
    .registre-table th, .registre-table td {
        padding: 2px 3px;
        text-align: center;
    }
    .header-section {
        text-align: center;
        margin-bottom: 10px;
    }
    .registre-title {
        font-weight: bold;
        font-size: 13px;
        margin: 6px 0;
    }
    .subtitle {
        font-size: 11px;
        margin-bottom: 3px;
    }
    .logo-placeholder {
        width: 70px;
        height: 50px;
        border: 1px solid #999;
        display: inline-block;
        vertical-align: middle;
        margin-left: 15px;
    }
    .total-souscompetence {
        background-color: #fff9c4;
    }
    .rang {
        font-weight: bold;
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
</style>

<div class="header-section">
    <div>REPUBLIQUE DU CAMEROUN</div>
    <div>PAIX - TRAVAIL - PATRIE</div>
    <div>MINISTERE DE L'EDUCATION DE BASE
        <span class="logo-placeholder">LOGO</span>
    </div>
    <div class="registre-title">REGISTRE DE NOTES</div>
    <div class="subtitle">Année scolaire : <b>{{ $session->nom }}</b> | Classe : <b>{{ $classe->nom }}</b></div>
</div>

<div class="table-responsive">
    <table class="registre-table">
        <thead>
            <!-- Row 1: Competence Label -->
            <tr>
                <th rowspan="4">#</th>
               
                <th rowspan="4">Nom et prénom</th>
                @foreach($ordreCompetences as $comp)
                    @php
                        $compSpan = 0;
                        foreach($comp['sous'] as $sc) {
                            // +1 for total_sc column
                            $compSpan += count($sc['modalites']) + 1;
                        }
                    @endphp
                    <th colspan="{{ $compSpan }}">{{ $comp['label'] }}</th>
                @endforeach
                <th rowspan="4">Total</th>
                <th rowspan="4">Moyenne/20</th>
                <th rowspan="4">Cote</th>
                <th rowspan="4">Rang</th>
            </tr>

            <!-- Row 2: Sous-Competence Label -->
            <tr>
                @foreach($ordreCompetences as $comp)
                    @foreach($comp['sous'] as $sc)
                        <th colspan="{{ count($sc['modalites']) + 1 }}">{{ $sc['label'] }}</th>
                    @endforeach
                @endforeach
            </tr>

            <!-- Row 3: Modalite Label -->
            <tr>
                @foreach($ordreCompetences as $comp)
                    @foreach($comp['sous'] as $sc)
                        @foreach($sc['modalites'] as $modaliteNom)
                            <th>{{ $modaliteNom }}</th>
                        @endforeach
                        <th>Total SC</th>
                    @endforeach
                @endforeach
            </tr>

            <!-- Row 4: Points Max -->
            <tr>
                @foreach($ordreCompetences as $comp)
                    @foreach($comp['sous'] as $sc)
                        @php $totalPointsMaxSc = 0; @endphp
                        @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                            @php
                                $points = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                                $totalPointsMaxSc += $points;
                            @endphp
                            <th>/{{ $points }}</th>
                        @endforeach
                        <th>/{{ $totalPointsMaxSc }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($eleves as $eleve)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                 
                    <td>{{ $eleve['eleve'] ? $eleve['eleve']->nom . ' ' . $eleve['eleve']->prenom : '-' }}</td>
                    
                    @php
                        $grandTotalEleve = 0;
                        $totalPointsMaxEleve = 0;
                    @endphp

                    @foreach($ordreCompetences as $comp)
                        @foreach($comp['sous'] as $sc)
                            @php
                            $scId = $sousCompetenceIds[$sc['label']] ?? null;
                            $totalSousComp = 0;
                            $totalPointsMaxSousComp = 0;
                        @endphp

                        @foreach($sc['modalites'] as $mIdx => $modaliteNom)
                            @php
                                $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                                $val = '-';
                                $pointsMax = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                                
                                if ($eleve['eleve'] && $scId && $modaliteId && isset($notes[$eleve['eleve']->id][$scId]) && isset($notes[$eleve['eleve']->id][$scId][$modaliteId])) {
                                    $val = $notes[$eleve['eleve']->id][$scId][$modaliteId];
                                    if (is_numeric($val)) {
                                        $totalSousComp += $val;
                                    }
                                }
                                $totalPointsMaxSousComp += $pointsMax;
                            @endphp
                            <td>
    @if(is_numeric($val))
        {{ round($val) }}
    @else
        {{ $val }}
    @endif
</td>
                            @endforeach

                            {{-- Total Sous-compétence --}}
                            <td class="total-souscompetence" data-points-max-sc="{{ $totalPointsMaxSousComp }}">
                                {{ $totalSousComp > 0 ? $totalSousComp : '-' }}
                            </td>
                            
                            @php
                                $grandTotalEleve += $totalSousComp;
                                $totalPointsMaxEleve += $totalPointsMaxSousComp;
                            @endphp
                        @endforeach
                    @endforeach

                    {{-- Colonnes finales --}}
                    <td class="total-general">{{ $grandTotalEleve > 0 ? $grandTotalEleve : '-' }}</td>
                    <td class="moyenne">
                        @if($totalPointsMaxEleve > 0)
                            {{ number_format(($grandTotalEleve / $totalPointsMaxEleve) * 20, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="cote">
                        @php
                            $moyenne = $totalPointsMaxEleve > 0 ? ($grandTotalEleve / $totalPointsMaxEleve) * 20 : 0;
                            $cote = $moyenne >= 16 ? 'A' : ($moyenne >= 14 ? 'B' : ($moyenne >= 12 ? 'C' : ($moyenne >= 10 ? 'D' : 'E')));
                        @endphp
                        {{ $totalPointsMaxEleve > 0 ? $cote : '-' }}
                    </td>
                    <td class="rang">{{ $eleve['rang'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center">Aucun élève trouvé pour cette classe.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


