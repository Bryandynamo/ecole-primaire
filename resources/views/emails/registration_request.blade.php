<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande de création de compte</title>
    <style>
        .btn { padding: 10px 14px; border-radius: 6px; text-decoration: none; color: #fff; display: inline-block; margin-right: 8px; }
        .approve { background: #16a34a; }
        .reject { background: #dc2626; }
        .card { border:1px solid #e5e7eb; border-radius: 8px; padding:16px; font-family: Arial, Helvetica, sans-serif; }
        .muted { color:#6b7280; font-size: 12px; }
        h1 { font-size:18px; margin:0 0 10px 0; }
        h2 { font-size:14px; margin:14px 0 8px 0; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: 4px 0; vertical-align: top; }
        .label { color:#374151; width: 180px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Nouvelle demande de création de compte</h1>
    <p>Vous avez reçu une nouvelle demande d'inscription. Voici les détails:</p>

    <table>
        <tr>
            <td class="label">Nom complet</td>
            <td>{{ $rr->name }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $rr->email }}</td>
        </tr>
        <tr>
            <td class="label">Comptes existants</td>
            <td>{{ $usersCount }}</td>
        </tr>
    </table>

    @php($m = is_array($rr->meta) ? $rr->meta : [])
    @if(!empty($m))
        <h2>Informations Enseignant (si fournies)</h2>
        <table>
            <tr><td class="label">Nom</td><td>{{ $m['nom'] ?? '—' }}</td></tr>
            <tr><td class="label">Prénom</td><td>{{ $m['prenom'] ?? '—' }}</td></tr>
            <tr><td class="label">Matricule</td><td>{{ $m['matricule'] ?? '—' }}</td></tr>
            <tr>
                <td class="label">Établissement</td>
                <td>
                    @if(!empty($etabName))
                        {{ $etabName }}
                    @elseif(!empty($m['etablissement_id']))
                        ID {{ $m['etablissement_id'] }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Classe</td>
                <td>
                    @if(!empty($classeName))
                        {{ $classeName }}
                    @elseif(!empty($m['classe_id']))
                        ID {{ $m['classe_id'] }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr><td class="label">Code d'invitation</td><td>{{ $m['invite_code'] ?? '—' }}</td></tr>
        </table>
    @endif

    <p>
        <a class="btn approve" href="{{ $approveUrl }}">Approuver</a>
        <a class="btn reject" href="{{ $rejectUrl }}">Rejeter</a>
    </p>

    <p class="muted">Ces liens sont sécurisés (signés) et expirent dans 48 heures.</p>
</div>
</body>
</html>
