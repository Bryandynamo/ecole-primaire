<div class="container mt-4">
    <h5 class="text-center mb-3">Synthèse des compétences par sous-compétence<br>
        <span style="font-size:0.95em; font-weight:normal;">Classe : <b>{{ $classe->nom }}</b> | Évaluation : <b>{{ $evaluation->libelle ?? $evaluation->id }}</b></span>
    </h5>
    <div class="table-responsive">
        @if(empty($synthese) || count($synthese) === 0)
            <div class="alert alert-warning text-center">Aucune donnée de synthèse disponible pour cette évaluation.<br>
                <small>session_id={{ $session->id }}, classe_id={{ $classe->id }}, evaluation_id={{ $evaluation->id }}</small>
            </div>
        @else
        <table class="table table-bordered table-sm" style="min-width:900px;">
            <thead class="table-light">
                <tr>
                    <th>Sous-compétence</th>
                    <th>Inscrits</th>
                    <th>Présents</th>
                    <th colspan="2">Experts (≥18)</th>
                    <th colspan="2">Acquis (15-17.99)</th>
                    <th colspan="2">En cours (10-14.99)</th>
                    <th colspan="2">Non acquis (<10)</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Nb</th><th>%</th>
                    <th>Nb</th><th>%</th>
                    <th>Nb</th><th>%</th>
                    <th>Nb</th><th>%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($synthese as $row)
                <tr>
                    <td>{{ $row['sous_competence'] }}</td>
                    <td>{{ $row['inscrits'] }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['experts'] }}</td>
                    <td>{{ $row['experts_pourc'] }}%</td>
                    <td>{{ $row['acquis'] }}</td>
                    <td>{{ $row['acquis_pourc'] }}%</td>
                    <td>{{ $row['encours'] }}</td>
                    <td>{{ $row['encours_pourc'] }}%</td>
                    <td>{{ $row['nonacquis'] }}</td>
                    <td>{{ $row['nonacquis_pourc'] }}%</td>
                </tr>
                <tr class="table-secondary small">
                    <td>dont Garçons</td>
                    <td></td>
                    <td></td>
                    <td>{{ $row['experts_g'] }}</td><td></td>
                    <td>{{ $row['acquis_g'] }}</td><td></td>
                    <td>{{ $row['encours_g'] }}</td><td></td>
                    <td>{{ $row['nonacquis_g'] }}</td><td></td>
                </tr>
                <tr class="table-secondary small">
                    <td>dont Filles</td>
                    <td></td>
                    <td></td>
                    <td>{{ $row['experts_f'] }}</td><td></td>
                    <td>{{ $row['acquis_f'] }}</td><td></td>
                    <td>{{ $row['encours_f'] }}</td><td></td>
                    <td>{{ $row['nonacquis_f'] }}</td><td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    <div class="text-end mt-2" style="font-size:0.95rem; color:#888;">
        Synthèse générée le {{ now()->format('d/m/Y H:i') }}
    </div>
</div> 