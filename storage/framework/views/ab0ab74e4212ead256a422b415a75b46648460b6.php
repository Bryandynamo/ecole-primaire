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
            <td><?php echo e($rr->name); ?></td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td><?php echo e($rr->email); ?></td>
        </tr>
        <tr>
            <td class="label">Comptes existants</td>
            <td><?php echo e($usersCount); ?></td>
        </tr>
    </table>

    <?php ($m = is_array($rr->meta) ? $rr->meta : []); ?>
    <?php if(!empty($m)): ?>
        <h2>Informations Enseignant (si fournies)</h2>
        <table>
            <tr><td class="label">Nom</td><td><?php echo e($m['nom'] ?? '—'); ?></td></tr>
            <tr><td class="label">Prénom</td><td><?php echo e($m['prenom'] ?? '—'); ?></td></tr>
            <tr><td class="label">Matricule</td><td><?php echo e($m['matricule'] ?? '—'); ?></td></tr>
            <tr>
                <td class="label">Établissement</td>
                <td>
                    <?php if(!empty($etabName)): ?>
                        <?php echo e($etabName); ?>

                    <?php elseif(!empty($m['etablissement_id'])): ?>
                        ID <?php echo e($m['etablissement_id']); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Classe</td>
                <td>
                    <?php if(!empty($classeName)): ?>
                        <?php echo e($classeName); ?>

                    <?php elseif(!empty($m['classe_id'])): ?>
                        ID <?php echo e($m['classe_id']); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr><td class="label">Code d'invitation</td><td><?php echo e($m['invite_code'] ?? '—'); ?></td></tr>
        </table>
    <?php endif; ?>

    <p>
        <a class="btn approve" href="<?php echo e($approveUrl); ?>">Approuver</a>
        <a class="btn reject" href="<?php echo e($rejectUrl); ?>">Rejeter</a>
    </p>

    <p class="muted">Ces liens sont sécurisés (signés) et expirent dans 48 heures.</p>
</div>
</body>
</html>
<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/emails/registration_request.blade.php ENDPATH**/ ?>