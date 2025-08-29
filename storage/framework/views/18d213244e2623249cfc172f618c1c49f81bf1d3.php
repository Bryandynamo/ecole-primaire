<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Synthèse des compétences par compétence</h4>
        <div class="btn-group" role="group" aria-label="Exports">
            <a href="<?php echo e(route('registre.syntheseCompetencesPdf', [$classe->session_id ?? $session->id, $classe->id, $evaluation->id])); ?>" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="<?php echo e(route('registre.syntheseCompetencesExcel', [$classe->session_id ?? $session->id, $classe->id, $evaluation->id])); ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>
    <div class="mb-2" style="font-size:1.1em;">
        <b>Classe :</b> <?php echo e($classe->nom); ?><br>
        <b>Évaluation :</b> <?php echo e($evaluation->libelle ?? $evaluation->id); ?><br>
        <b>Année scolaire :</b> <?php echo e($session->nom); ?>

    </div>

    <div class="table-responsive">
        <?php if(empty($synthese) || count($synthese) === 0): ?>
            <div class="alert alert-warning text-center">Aucune donnée de synthèse disponible pour cette évaluation.</div>
        <?php else: ?>
        <table class="table table-bordered table-sm" style="min-width:1800px;">
            <thead class="table-light">
                <tr>
                    <th rowspan="3" class="align-middle">Contenus compétence</th>
                    <th colspan="3">inscrits</th>
                    <th colspan="3">Presents</th>
                    <th colspan="6">Experts</th>
                    <th colspan="6">Acquis</th>
                    <th colspan="6">En cours d'acquisition</th>
                    <th colspan="6">non acquis</th>
                </tr>
                <tr>
                    <th>G</th><th>F</th><th>T</th>
                    <th>G</th><th>F</th><th>T</th>
                    <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                    <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                    <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                    <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $synthese; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($row['sous_competence']); ?></td>
                    <td><?php echo e($row['inscrits_g']); ?></td>
                    <td><?php echo e($row['inscrits_f']); ?></td>
                    <td><?php echo e($row['inscrits_t']); ?></td>
                    <td><?php echo e($row['present_g']); ?></td>
                    <td><?php echo e($row['present_f']); ?></td>
                    <td><?php echo e($row['present_t']); ?></td>
                    <td><?php echo e($row['experts_g']); ?></td>
                    <td><?php echo e($row['experts_f']); ?></td>
                    <td><?php echo e($row['experts_t']); ?></td>
                    <td><?php echo e($row['experts_g_p']); ?></td>
                    <td><?php echo e($row['experts_f_p']); ?></td>
                    <td><?php echo e($row['experts_t_p']); ?></td>
                    <td><?php echo e($row['acquis_g']); ?></td>
                    <td><?php echo e($row['acquis_f']); ?></td>
                    <td><?php echo e($row['acquis_t']); ?></td>
                    <td><?php echo e($row['acquis_g_p']); ?></td>
                    <td><?php echo e($row['acquis_f_p']); ?></td>
                    <td><?php echo e($row['acquis_t_p']); ?></td>
                    <td><?php echo e($row['encours_g']); ?></td>
                    <td><?php echo e($row['encours_f']); ?></td>
                    <td><?php echo e($row['encours_t']); ?></td>
                    <td><?php echo e($row['encours_g_p']); ?></td>
                    <td><?php echo e($row['encours_f_p']); ?></td>
                    <td><?php echo e($row['encours_t_p']); ?></td>
                    <td><?php echo e($row['nonacquis_g']); ?></td>
                    <td><?php echo e($row['nonacquis_f']); ?></td>
                    <td><?php echo e($row['nonacquis_t']); ?></td>
                    <td><?php echo e($row['nonacquis_g_p']); ?></td>
                    <td><?php echo e($row['nonacquis_f_p']); ?></td>
                    <td><?php echo e($row['nonacquis_t_p']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <?php if(isset($totaux)): ?>
            <tfoot>
                <tr style="background: #f2f2f2; font-weight: bold;">
                    <td>TOTAL</td>
                    <td><?php echo e($totaux['inscrits_g']); ?></td>
                    <td><?php echo e($totaux['inscrits_f']); ?></td>
                    <td><?php echo e($totaux['inscrits_t']); ?></td>
                    <td><?php echo e($totaux['present_g']); ?></td>
                    <td><?php echo e($totaux['present_f']); ?></td>
                    <td><?php echo e($totaux['present_t']); ?></td>
                    <td><?php echo e($totaux['experts_g']); ?></td>
                    <td><?php echo e($totaux['experts_f']); ?></td>
                    <td><?php echo e($totaux['experts_t']); ?></td>
                    <td><?php echo e($totaux['experts_g_p']); ?></td>
                    <td><?php echo e($totaux['experts_f_p']); ?></td>
                    <td><?php echo e($totaux['experts_t_p']); ?></td>
                    <td><?php echo e($totaux['acquis_g']); ?></td>
                    <td><?php echo e($totaux['acquis_f']); ?></td>
                    <td><?php echo e($totaux['acquis_t']); ?></td>
                    <td><?php echo e($totaux['acquis_g_p']); ?></td>
                    <td><?php echo e($totaux['acquis_f_p']); ?></td>
                    <td><?php echo e($totaux['acquis_t_p']); ?></td>
                    <td><?php echo e($totaux['encours_g']); ?></td>
                    <td><?php echo e($totaux['encours_f']); ?></td>
                    <td><?php echo e($totaux['encours_t']); ?></td>
                    <td><?php echo e($totaux['encours_g_p']); ?></td>
                    <td><?php echo e($totaux['encours_f_p']); ?></td>
                    <td><?php echo e($totaux['encours_t_p']); ?></td>
                    <td><?php echo e($totaux['nonacquis_g']); ?></td>
                    <td><?php echo e($totaux['nonacquis_f']); ?></td>
                    <td><?php echo e($totaux['nonacquis_t']); ?></td>
                    <td><?php echo e($totaux['nonacquis_g_p']); ?></td>
                    <td><?php echo e($totaux['nonacquis_f_p']); ?></td>
                    <td><?php echo e($totaux['nonacquis_t_p']); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="text-end mt-2" style="font-size:0.95rem; color:#888;">
        Synthèse générée le <?php echo e(now()->format('d/m/Y H:i')); ?>

    </div>
    <div class="mt-3">
        <a href="<?php echo e(route('registre.index')); ?>" class="btn btn-secondary">Retour</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php if(request()->routeIs('registre.syntheseCompetencesPdf')): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Synthèse des compétences</title>
    <style>
        body { font-size: 10px; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 9px; }
        th { background: #f5f5f5; }
        .align-middle { vertical-align: middle; }
        .pdf-title { font-size: 1.2em; font-weight: bold; text-align: center; margin-bottom: 4px; }
        .pdf-info { font-size: 1em; text-align: center; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="pdf-title">Synthèse des compétences par sous-compétence</div>
    <div class="pdf-info">
        Classe : <?php echo e($classe->nom); ?><br>
        Évaluation : <?php echo e($evaluation->libelle ?? $evaluation->id); ?><br>
        Année scolaire : <?php echo e($session->nom); ?>

    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="3" class="align-middle">Contenus sous-compétence</th>
                <th colspan="3">inscrits</th>
                <th colspan="3">Presents</th>
                <th colspan="6">Experts</th>
                <th colspan="6">Acquis</th>
                <th colspan="6">En cours d'acquisition</th>
                <th colspan="6">non acquis</th>
            </tr>
            <tr>
                <th>G</th><th>F</th><th>T</th>
                <th>G</th><th>F</th><th>T</th>
                <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
                <th>G</th><th>F</th><th>T</th><th>G %</th><th>F %</th><th>T %</th>
            </tr>
            <tr>
                <th></th><th></th><th></th>
                <th></th><th></th><th></th>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th></th><th></th><th></th><th></th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $synthese; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($row['sous_competence']); ?></td>
                <td><?php echo e($row['inscrits_g']); ?></td>
                <td><?php echo e($row['inscrits_f']); ?></td>
                <td><?php echo e($row['inscrits_t']); ?></td>
                <td><?php echo e($row['present_g']); ?></td>
                <td><?php echo e($row['present_f']); ?></td>
                <td><?php echo e($row['present_t']); ?></td>
                <td><?php echo e($row['experts_g']); ?></td>
                <td><?php echo e($row['experts_f']); ?></td>
                <td><?php echo e($row['experts_t']); ?></td>
                <td><?php echo e($row['experts_g_p']); ?></td>
                <td><?php echo e($row['experts_f_p']); ?></td>
                <td><?php echo e($row['experts_t_p']); ?></td>
                <td><?php echo e($row['acquis_g']); ?></td>
                <td><?php echo e($row['acquis_f']); ?></td>
                <td><?php echo e($row['acquis_t']); ?></td>
                <td><?php echo e($row['acquis_g_p']); ?></td>
                <td><?php echo e($row['acquis_f_p']); ?></td>
                <td><?php echo e($row['acquis_t_p']); ?></td>
                <td><?php echo e($row['encours_g']); ?></td>
                <td><?php echo e($row['encours_f']); ?></td>
                <td><?php echo e($row['encours_t']); ?></td>
                <td><?php echo e($row['encours_g_p']); ?></td>
                <td><?php echo e($row['encours_f_p']); ?></td>
                <td><?php echo e($row['encours_t_p']); ?></td>
                <td><?php echo e($row['nonacquis_g']); ?></td>
                <td><?php echo e($row['nonacquis_f']); ?></td>
                <td><?php echo e($row['nonacquis_t']); ?></td>
                <td><?php echo e($row['nonacquis_g_p']); ?></td>
                <td><?php echo e($row['nonacquis_f_p']); ?></td>
                <td><?php echo e($row['nonacquis_t_p']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php endif; ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/registre/synthese-competences-page.blade.php ENDPATH**/ ?>