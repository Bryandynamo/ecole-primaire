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
    <div class="subtitle">Année scolaire : <b><?php echo e($session->nom); ?></b> | Classe : <b><?php echo e($classe->nom); ?></b></div>
</div>

<div class="table-responsive">
    <table class="registre-table">
        <thead>
            <!-- Row 1: Competence Label -->
            <tr>
                <th rowspan="4">#</th>
               
                <th rowspan="4">Nom et prénom</th>
                <?php $__currentLoopData = $ordreCompetences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $compSpan = 0;
                        foreach($comp['sous'] as $sc) {
                            // +1 for total_sc column
                            $compSpan += count($sc['modalites']) + 1;
                        }
                    ?>
                    <th colspan="<?php echo e($compSpan); ?>"><?php echo e($comp['label']); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th rowspan="4">Total</th>
                <th rowspan="4">Moyenne/20</th>
                <th rowspan="4">Cote</th>
                <th rowspan="4">Rang</th>
            </tr>

            <!-- Row 2: Sous-Competence Label -->
            <tr>
                <?php $__currentLoopData = $ordreCompetences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $comp['sous']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th colspan="<?php echo e(count($sc['modalites']) + 1); ?>"><?php echo e($sc['label']); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <!-- Row 3: Modalite Label -->
            <tr>
                <?php $__currentLoopData = $ordreCompetences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $comp['sous']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $sc['modalites']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modaliteNom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($modaliteNom); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th>Total SC</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            <!-- Row 4: Points Max -->
            <tr>
                <?php $__currentLoopData = $ordreCompetences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $comp['sous']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $totalPointsMaxSc = 0; ?>
                        <?php $__currentLoopData = $sc['modalites']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mIdx => $modaliteNom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $points = $pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                                $totalPointsMaxSc += $points;
                            ?>
                            <th>/<?php echo e($points); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th>/<?php echo e($totalPointsMaxSc); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $eleves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eleve): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                 
                    <td><?php echo e($eleve['eleve'] ? $eleve['eleve']->nom . ' ' . $eleve['eleve']->prenom : '-'); ?></td>
                    
                    <?php
                        $grandTotalEleve = 0;
                        $totalPointsMaxEleve = 0;
                    ?>

                    <?php $__currentLoopData = $ordreCompetences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $comp['sous']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $scId = $sousCompetenceIds[$sc['label']] ?? null;
                            $totalSousComp = 0;
                            $totalPointsMaxSousComp = 0;
                        ?>

                        <?php $__currentLoopData = $sc['modalites']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mIdx => $modaliteNom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
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
                            ?>
                            <td>
    <?php if(is_numeric($val)): ?>
        <?php echo e(round($val)); ?>

    <?php else: ?>
        <?php echo e($val); ?>

    <?php endif; ?>
</td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <td class="total-souscompetence" data-points-max-sc="<?php echo e($totalPointsMaxSousComp); ?>">
                                <?php echo e($totalSousComp > 0 ? $totalSousComp : '-'); ?>

                            </td>
                            
                            <?php
                                $grandTotalEleve += $totalSousComp;
                                $totalPointsMaxEleve += $totalPointsMaxSousComp;
                            ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <td class="total-general"><?php echo e($grandTotalEleve > 0 ? $grandTotalEleve : '-'); ?></td>
                    <td class="moyenne">
                        <?php if($totalPointsMaxEleve > 0): ?>
                            <?php echo e(number_format(($grandTotalEleve / $totalPointsMaxEleve) * 20, 2)); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="cote">
                        <?php
                            $moyenne = $totalPointsMaxEleve > 0 ? ($grandTotalEleve / $totalPointsMaxEleve) * 20 : 0;
                            $cote = $moyenne >= 16 ? 'A' : ($moyenne >= 14 ? 'B' : ($moyenne >= 12 ? 'C' : ($moyenne >= 10 ? 'D' : 'E')));
                        ?>
                        <?php echo e($totalPointsMaxEleve > 0 ? $cote : '-'); ?>

                    </td>
                    <td class="rang"><?php echo e($eleve['rang'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="100%" class="text-center">Aucun élève trouvé pour cette classe.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/registre/pdf.blade.php ENDPATH**/ ?>