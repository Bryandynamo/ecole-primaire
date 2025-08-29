<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - {{ $classe->nom }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        .header, .footer { width: 100%; text-align: center; position: fixed; }
        .header { top: 0px; }
        .footer { bottom: 0px; font-size: 8px; }
        .content { margin-top: 50px; margin-bottom: 50px; width: 100%; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div style="text-align:center; margin-bottom:18px;">
        <h2 style="margin-bottom: 2px;">
            @if($periode === 'annee')
                Statistiques annuelles
            @else
                Statistiques du {{ $periode }}<sup>e</sup> trimestre
            @endif
        </h2>
        <div style="font-size: 13px; font-weight: bold;">
            Classe : {{ $classe->nom }} |
            Année scolaire :
            @php
                $anneeScolaire = '';
                if (isset($session->date_debut) && isset($session->date_fin)) {
                    $anneeScolaire = date('Y', strtotime($session->date_debut)) . '/' . date('Y', strtotime($session->date_fin));
                } elseif(isset($session->nom)) {
                    $anneeScolaire = $session->nom;
                } elseif(isset($annee)) {
                    $anneeScolaire = $annee;
                }
            @endphp
            {{ $anneeScolaire }}
        </div>
    </div>

    <div class="content">
        @if(!empty($statistiques))
        <div class="table-responsive">
<table class="table" style="margin-bottom: 24px;">
            <thead style="background-color: #f2f2f2;">
                <tr>
                    <th>Catégorie</th>
                    <th class="btn btn-primary w-100 mb-2">Classe</th>
                    <th class="btn btn-primary w-100 mb-2">Garçons</th>
                    <th class="btn btn-primary w-100 mb-2">Filles</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Inscrits</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['inscrits'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['inscrits_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['inscrits_filles'] }}</td></tr>
                <tr><td>Ayant composé</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['ayant_compose'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['ayant_compose_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['ayant_compose_filles'] }}</td></tr>
                <tr><td>Admis (moy ≥ 10)</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['admis'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['admis_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['admis_filles'] }}</td></tr>
                <tr><td>Échoués (moy < 10)</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['echoues'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['echoues_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['echoues_filles'] }}</td></tr>
                <tr><td>% Réussite</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_reussite'] }}%</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_reussite_garcons'] }}%</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_reussite_filles'] }}%</td></tr>
                <tr><td>% Échec</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_echec'] }}%</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_echec_garcons'] }}%</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['pourc_echec_filles'] }}%</td></tr>
                <tr><td>Moyenne générale</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_generale'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_generale_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_generale_filles'] }}</td></tr>
                <tr><td>Moyenne du premier</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_premier'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_premier_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_premier_filles'] }}</td></tr>
                <tr><td>Moyenne du dernier</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_dernier'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_dernier_garcons'] }}</td><td class="btn btn-primary w-100 mb-2">{{ $statistiques['moyenne_dernier_filles'] }}</td></tr>
            </tbody>
        </table>
</div>
        @endif
        {{-- DEBUG TEMPORAIRE : Affiche le contenu de recapEvaluations --}}
        @if($type === 'annee')
            <div style="font-size:8px; color:#a00; word-break:break-all;">
                @php echo json_encode($recapEvaluations); @endphp
            </div>
        @endif
        @if($periode === 'annee' && !empty($statsUAs))
            <h5 style="margin-top: 10px; margin-bottom: 6px;">Récapitulatif par évaluation</h5>
            <div class="table-responsive">
<table class="table" style="font-size:11px; margin-bottom: 0;">
                <thead style="background-color: #f2f2f2;">
                    <tr>
                        <th>Évaluation</th>
                        <th class="btn btn-primary w-100 mb-2">Moyenne générale</th>
                        <th class="btn btn-primary w-100 mb-2">% Réussite</th>
                        <th class="btn btn-primary w-100 mb-2">Admis</th>
                        <th class="btn btn-primary w-100 mb-2">Échoués</th>
                        <th class="btn btn-primary w-100 mb-2">Ayant composé</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statsUAs as $ua => $stats_ua)
                        <tr>
                            <td><strong>{{ $ua }}e évaluation</strong></td>
                            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['moyenne_generale'] ?? '-' }}</td>
                            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['pourc_reussite'] ?? '-' }}%</td>
                            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['admis'] ?? '-' }}</td>
                            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['echoues'] ?? '-' }}</td>
                            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['ayant_compose'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
        @endif
        @if($type === 'trimestre' && !empty($statsUAs))
            <h5>Récapitulatif par évaluation</h5>
            <div class="table-responsive">
<table class="table" style="font-size:11px; margin-bottom: 10px;">
                <thead style="background-color: #f2f2f2;">
                    <tr>
                        <th>Évaluation</th>
                        <th class="btn btn-primary w-100 mb-2">Moyenne générale</th>
                        <th class="btn btn-primary w-100 mb-2">% Réussite</th>
                        <th class="btn btn-primary w-100 mb-2">Admis</th>
                        <th class="btn btn-primary w-100 mb-2">Échoués</th>
                        <th class="btn btn-primary w-100 mb-2">Ayant composé</th>
                    </tr>
                </thead>
                <tbody>
@php
    $debutUA = ($periode - 1) * 3 + 1;
    $finUA = $periode * 3;
@endphp
@for($ua = $debutUA; $ua <= $finUA; $ua++)
    @php
        $stats_ua = $statsUAs[$ua] ?? null;
    @endphp
    <tr>
        <td><strong>{{ $ua }}e évaluation</strong></td>
        @if($stats_ua)
            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['moyenne_generale'] ?? '-' }}</td>
            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['pourc_reussite'] ?? '-' }}%</td>
            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['admis'] ?? '-' }}</td>
            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['echoues'] ?? '-' }}</td>
            <td class="btn btn-primary w-100 mb-2">{{ $stats_ua['ayant_compose'] ?? '-' }}</td>
        @else
            <td colspan="5" class="btn btn-primary w-100 mb-2">Aucune donnée disponible</td>
        @endif
    </tr>
@endfor
                </tbody>
            </table>
</div>
        @endif
    </div>

    <div class="footer">
        <p>Généré le {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
