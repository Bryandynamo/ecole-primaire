<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif de la Couverture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Styles spécifiques pour le PDF, conditionnés par la variable $is_pdf */
    <?php if(isset($is_pdf) && $is_pdf): ?>
    body {
        font-family: 'DejaVu Sans', sans-serif;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th, td {
        border: 1px solid #666; /* Bordures plus fines et grises */
        padding: 4px;
        text-align: center;
        font-size: 9px; /* Police légèrement réduite pour l'équilibre */
        word-wrap: break-word;
        vertical-align: middle; /* Centrage vertical parfait */
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    th.col-numero { width: 3%; }
    th.col-discipline { width: 15%; }
    th.col-lecon { width: 22%; }
    th.col-ua { width: 4%; }
    th.col-total { width: 5%; }
    th.col-annuel { width: 6%; }
    .discipline-nom, .lecon-nom { text-align: left; padding-left: 5px; }

    .no-export {
        display: none !important;
    }

    thead {
        display: table-header-group; /* Répéter l'en-tête sur chaque page */
    }
    tbody {
        page-break-inside: avoid !important; /* La règle CLÉ : ne jamais couper un bloc discipline */
    }
    <?php endif; ?>
</style>
</head>
<body>
<div class="container-fluid">
    <?php if(!isset($is_pdf) || !$is_pdf): ?>
        <a href="<?php echo e(route('registre.index')); ?>" class="btn btn-secondary mb-3">Retour</a>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-center">Récapitulatif de la couverture – Session : <?php echo e($session->nom); ?> – Classe : <?php echo e($classe->nom); ?></h1>
            <div class="btn-group">
                <a href="<?php echo e(route('couverture.recap.pdf', ['classe' => $classe->id])); ?>" class="btn btn-primary">Exporter en PDF</a>
                <a href="<?php echo e(route('couverture.recap.excel', ['classe' => $classe->id])); ?>" class="btn btn-success">Exporter en Excel</a>
            </div>
        </div>
    <?php else: ?>
        <h3 class="text-center font-weight-bold mb-3">Récapitulatif de la couverture – Session : <?php echo e($session->nom); ?> – Classe : <?php echo e($classe->nom); ?></h3>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="col-numero">N°</th>
                    <th class="col-discipline">Discipline (Sous-compétence)</th>
                    <th class="col-lecon">Leçons</th>
                    <?php $__currentLoopData = $uasByTrimestre; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trimestre => $uas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $uas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="col-ua">UA<?php echo e($ua->numero_eval); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="col-total">Total T<?php echo e($trimestre); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th class="col-annuel">Total Annuel</th>
                </tr>
            </thead>

            <?php $counter = 1; ?>
            <?php $__currentLoopData = $recapData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($data['lecons']) > 0): ?>
                <tbody>
                    <?php $__currentLoopData = $data['lecons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $lecon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <?php if($index === 0): ?>
                                <td rowspan="<?php echo e(count($data['lecons'])); ?>"><?php echo e($counter++); ?></td>
                                <td rowspan="<?php echo e(count($data['lecons'])); ?>" class="discipline-nom"><?php echo e($data['nom']); ?></td>
                            <?php endif; ?>
                            <td class="lecon-nom"><?php echo e($lecon['nom']); ?></td>
                            <?php $__currentLoopData = $uasByTrimestre; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trimestre => $uas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $__currentLoopData = $uas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td><?php echo e($lecon['totaux']['ua_' . $ua->numero_eval] ?? 0); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="fw-bold"><?php echo e($lecon['totaux']['trimestre_' . $trimestre] ?? 0); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="fw-bold"><?php echo e($lecon['totaux']['annuel'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </div>
</div>

<script>
// La logique AJAX pour récupérer et mettre à jour les données sera ajoutée ici plus tard.
</script>

</body>
</html>
<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/couverture/recap.blade.php ENDPATH**/ ?>