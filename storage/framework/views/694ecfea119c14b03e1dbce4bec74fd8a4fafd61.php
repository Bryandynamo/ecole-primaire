<?php $__env->startSection('content'); ?>
<style>
@media print {
    .btn, .alert, .debug, .stat-debug { display: none !important; }
    .stat-title, .stat-sub, .stat-table, .table { display: block !important; }
    body { background: white; }
    .container { margin: 0; padding: 0; max-width: 100vw; }
    table { page-break-inside: avoid; }
    h5 { font-size: 1.2rem; margin: 15px 0; }
}
.stat-title { text-align:center; font-weight:bold; font-size:1.4rem; margin-bottom:10px; }
.stat-sub { text-align:center; font-size:1.1rem; margin-bottom:15px; }
.stat-table { margin:auto; min-width:400px; max-width:900px; background:#fff; }
.stat-table th, .stat-table td { text-align:center; font-size:1.05rem; }
</style>
<div class="container mt-4">
    <?php
        // Déterminer le libellé selon le type
        $libelle = '';
        $titre = '';
        if ($periode === 'annee') {
            $libelle = 'toute l\'année';
            $titre = 'Statistiques annuelles';
        } elseif ($type === 'trimestre') {
            $trimestreNum = (int)$periode;
            if ($trimestreNum == 1) $libelle = '1er trimestre';
            elseif ($trimestreNum == 2) $libelle = '2e trimestre';
            elseif ($trimestreNum == 3) $libelle = '3e trimestre';
            else $libelle = $trimestreNum . 'e trimestre';
            $titre = 'Statistiques du ' . $libelle;
        } else {
            // Type UA
            $evalNum = (int)$periode;
            if ($evalNum == 1) $libelle = '1ère évaluation';
            elseif ($evalNum == 2) $libelle = '2e évaluation';
            elseif ($evalNum == 3) $libelle = '3e évaluation';
            else $libelle = $evalNum . 'e évaluation';
            $titre = 'Statistiques de ' . $libelle;
        }
    ?>
    <div class="stat-title"><?php echo e($titre); ?></div>
    
    <div class="alert alert-info" style="max-width:700px;margin:auto;">
        <b>NB&nbsp;:</b> Les statistiques ci-dessous concernent <u>uniquement</u> la classe <b><?php echo e($classe->nom ?? ''); ?></b>, pour <b><?php echo e($libelle); ?></b> de l'année <b><?php echo e($session->nom ?? ''); ?></b>.
    </div>
    
    <?php if($type === 'trimestre'): ?>
    <div class="alert alert-warning" style="max-width:700px;margin:auto;">
        <b>Récapitulatif des évaluations du <?php echo e($libelle); ?> :</b>
        <?php
            $debutUA = ($periode - 1) * 3 + 1;
            $finUA = $periode * 3;
        ?>
        <br>Ce trimestre comprend les évaluations de la <?php echo e($debutUA); ?>ère à la <?php echo e($finUA); ?>e évaluation.
    </div>
    
    <!-- Tableau récapitulatif des UAs du trimestre -->
    <div class="mt-4">
        <h5 class="text-center mb-3">Récapitulatif par évaluation</h5>
        <table class="table table-bordered table-sm" style="max-width:800px;margin:auto;">
            <thead class="table-light">
                <tr>
                    <th>Évaluation</th>
                    <th>Moyenne générale</th>
                    <th>% Réussite</th>
                    <th>Admis</th>
                    <th>Échoués</th>
                    <th>Ayant composé</th>
                </tr>
            </thead>
            <tbody>
                <?php for($ua = $debutUA; $ua <= $finUA; $ua++): ?>
                    <?php
                        $stats_ua = $statsUAs[$ua] ?? null;
                    ?>
                    <tr>
                        <td><strong><?php echo e($ua); ?>ère évaluation</strong></td>
                        <?php if($stats_ua): ?>
                            <td><?php echo e($stats_ua['moyenne_generale'] ?? '-'); ?></td>
                            <td><?php echo e($stats_ua['pourc_reussite'] ?? '-'); ?>%</td>
                            <td><?php echo e($stats_ua['admis'] ?? '-'); ?></td>
                            <td><?php echo e($stats_ua['echoues'] ?? '-'); ?></td>
                            <td><?php echo e($stats_ua['ayant_compose'] ?? '-'); ?></td>
                        <?php else: ?>
                            <td colspan="5" class="text-muted">Aucune donnée disponible</td>
                        <?php endif; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if(isset($recapEvaluations) && $periode === 'annee' && count($recapEvaluations) > 0): ?>
<div class="mt-4">
    <h5 class="text-center mb-3">Récapitulatif par évaluation</h5>
    <table class="table table-bordered table-sm" style="max-width:800px;margin:auto;">
        <thead class="table-light">
            <tr>
                <th>Évaluation</th>
                <th>Moyenne générale</th>
                <th>% Réussite</th>
                <th>Admis</th>
                <th>Échoués</th>
                <th>Ayant composé</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $recapEvaluations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $stats = $recap['stats'] ?? []; ?>
                <tr>
                    <td><strong><?php echo e($recap['numero_eval']); ?><sup>e</sup> évaluation</strong></td>
                    <td><?php echo e($stats['moyenne_generale'] ?? '-'); ?></td>
                    <td><?php echo e($stats['pourc_reussite'] ?? '-'); ?>%</td>
                    <td><?php echo e($stats['admis'] ?? '-'); ?></td>
                    <td><?php echo e($stats['echoues'] ?? '-'); ?></td>
                    <td><?php echo e($stats['ayant_compose'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div id="print-zone">
        <div class="stat-title">Statistiques de <?php echo e($libelle); ?></div>
        <div class="stat-sub">
            Classe : <b><?php echo e($classe->nom ?? ''); ?></b> |
            Année scolaire : <b><?php echo e($session->nom ?? ''); ?></b>
        </div>
        
        <table class="table table-bordered stat-table mt-3">
            <thead class="table-light">
                <tr><th>Catégorie</th><th>Classe</th><th>Garçons</th><th>Filles</th></tr>
            </thead>
            <tbody>
                <tr><td>Inscrits</td><td><?php echo e($statistiques['inscrits']); ?></td><td><?php echo e($statistiques['inscrits_garcons']); ?></td><td><?php echo e($statistiques['inscrits_filles']); ?></td></tr>
                <tr><td>Ayant composé</td><td><?php echo e($statistiques['ayant_compose']); ?></td><td><?php echo e($statistiques['ayant_compose_garcons']); ?></td><td><?php echo e($statistiques['ayant_compose_filles']); ?></td></tr>
                <tr><td>Admis (moy ≥ 10)</td><td><?php echo e($statistiques['admis']); ?></td><td><?php echo e($statistiques['admis_garcons']); ?></td><td><?php echo e($statistiques['admis_filles']); ?></td></tr>
                <tr><td>Échoués (moy < 10)</td><td><?php echo e($statistiques['echoues']); ?></td><td><?php echo e($statistiques['echoues_garcons']); ?></td><td><?php echo e($statistiques['echoues_filles']); ?></td></tr>
                <tr><td>% Réussite</td><td><?php echo e($statistiques['pourc_reussite']); ?>%</td><td><?php echo e($statistiques['pourc_reussite_garcons']); ?>%</td><td><?php echo e($statistiques['pourc_reussite_filles']); ?>%</td></tr>
                <tr><td>% Échec</td><td><?php echo e($statistiques['pourc_echec']); ?>%</td><td><?php echo e($statistiques['pourc_echec_garcons']); ?>%</td><td><?php echo e($statistiques['pourc_echec_filles']); ?>%</td></tr>
                <tr><td>Moyenne générale</td><td><?php echo e($statistiques['moyenne_generale']); ?></td><td><?php echo e($statistiques['moyenne_generale_garcons']); ?></td><td><?php echo e($statistiques['moyenne_generale_filles']); ?></td></tr>
                <tr><td>Moyenne du premier</td><td><?php echo e($statistiques['moyenne_premier']); ?></td><td><?php echo e($statistiques['moyenne_premier_garcons']); ?></td><td><?php echo e($statistiques['moyenne_premier_filles']); ?></td></tr>
                <tr><td>Moyenne du dernier</td><td><?php echo e($statistiques['moyenne_dernier']); ?></td><td><?php echo e($statistiques['moyenne_dernier_garcons']); ?></td><td><?php echo e($statistiques['moyenne_dernier_filles']); ?></td></tr>
            </tbody>
        </table>
        
        <div class="text-end mt-4" style="font-size:0.95rem; color:#888;">
            Fiche générée le <?php echo e(now()->format('d/m/Y H:i')); ?>

        </div>
    </div>
    
    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Imprimer</button>

        <form action="<?php echo e(route('registre.statistiques.pdf')); ?>" method="POST" style="display: inline-block;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="session_id" value="<?php echo e($session->id); ?>">
            <input type="hidden" name="classe_id" value="<?php echo e($classe->id); ?>">
            <input type="hidden" name="periode" value="<?php echo e($periode); ?>">
            <input type="hidden" name="type" value="<?php echo e($type); ?>">
            <button type="submit" class="btn btn-success">Exporter en PDF</button>
        </form>

        <form action="<?php echo e(route('registre.statistiques.excel')); ?>" method="POST" style="display: inline-block; margin-left:6px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="session_id" value="<?php echo e($session->id); ?>">
            <input type="hidden" name="classe_id" value="<?php echo e($classe->id); ?>">
            <input type="hidden" name="periode" value="<?php echo e($periode); ?>">
            <input type="hidden" name="type" value="<?php echo e($type); ?>">
            <button type="submit" class="btn btn-outline-success">Exporter en Excel</button>
        </form>

        <a href="<?php echo e(route('registre.index')); ?>" class="btn btn-secondary">Retour</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/registre/statistiques.blade.php ENDPATH**/ ?>