<?php
// Les variables attendues :
// $bulletins : tableau de bulletins, triés par mérite, chaque bulletin = ['eleve','classe','notes','totauxSousComp','totalGeneral','moyenne','structure','rang','profilClasse']
// $trimestre : trimestre de l'évaluation
// $bulletins[0]['notes'] contient les notes, $bulletins[0]['structure'] la structure
// On déduit dynamiquement la liste des UA à partir des notes du premier bulletin
$uaNums = [];
if (!empty($bulletins) && isset($bulletins[0]['notes'])) {
    // On prend la liste des UA du premier bloc de notes (elles sont alignées sur le builder)
    foreach ($bulletins[0]['notes'] as $scid => $modalites) {
        foreach ($modalites as $modid => $uas) {
            $uaNums = array_keys($uas);
            break 2;
        }
    }
    sort($uaNums, SORT_NUMERIC);
}
// Fonction pour convertir un nombre en format ordinal français
function rangOrdinal($nombre) {
    if ($nombre == 1) return '1er';
    if ($nombre == 2) return '2ème';
    if ($nombre == 3) return '3ème';
    if ($nombre == 4) return '4ème';
    if ($nombre == 5) return '5ème';
    if ($nombre == 6) return '6ème';
    if ($nombre == 7) return '7ème';
    if ($nombre == 8) return '8ème';
    if ($nombre == 9) return '9ème';
    if ($nombre == 10) return '10ème';
    return $nombre . 'ème';
}
// Déterminer si c'est une UA unique ou un trimestre
$isUaUnique = count($uaNums) == 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin Officiel</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/bulletin_officiel.css')); ?>">
    <style>
        @page { size: A4 landscape; margin: 3mm 3mm 3mm 3mm; }
        body { font-size: 8pt; margin: 0; padding: 0; }
        .page { page-break-after: always; height: 100vh; overflow: hidden; }
        .table-bulletin td, .table-bulletin th { padding: 0.3px 0.5px; font-size: 6pt; min-width: 15px; max-width: 30px; word-break: break-word; }
        .vertical-header { writing-mode: vertical-rl; transform: rotate(180deg); }
        .border { border: 0.2pt solid #000; }
        .b { font-weight: bold; }
        .center { text-align: center; }
        .small { font-size: 6pt; }
        .pad { padding: 0.5px; }
        .layout-container { display: flex; height: 100%; gap: 2mm; }
        .notes-section { flex: 2; }
        .stats-section { flex: 1; }
        .header-info { font-size: 7pt; margin-bottom: 1mm; }
    </style>
</head>
<body>
<?php $__currentLoopData = $bulletins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="page">
    <div class="layout-container">
        <!-- Section tableau de notes -->
        <div class="notes-section">
            <div class="header-info" style="margin-bottom: 1mm;">
                <strong>Élève:</strong> <?php echo e($b['eleve']['nom'] ?? ''); ?> <?php echo e($b['eleve']['prenom'] ?? ''); ?> | 
                <strong>Classe:</strong> <?php echo e($b['classe']['nom'] ?? ''); ?> | 
                <strong>Année:</strong> <?php echo e($annee ?? ''); ?>

            </div>
            <table class="table-bulletin" style="width:100%; border-collapse:collapse;">
                <tr>
                    <th rowspan="2" class="vertical-header border b">COMPÉTENCES</th>
                    <th rowspan="2" class="border b">SOUS COMPÉTENCES</th>
                    <th rowspan="2" class="border b">UNITÉS<br>NOTES</th>
                    <th colspan="<?php echo e(count($uaNums)*2); ?>" class="center border b">ANNUEL<br>Notes par UA</th>
                </tr>
                <tr>
                    <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th colspan="2" class="center border small">UA<?php echo e($ua); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <tr>
                    <th colspan="3" class="border"></th>
                    <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="border small">Notes</th>
                        <th class="border small">Cote</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <?php $__currentLoopData = $b['structure']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                        $compRowspan = collect($block['scs'])->reduce(function($t, $sc) { return $t + count($sc['mods']) + 1; }, 0);
                $firstCompRow = true;
            ?>
            <?php $__currentLoopData = $block['scs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                            $scRowspan = count($sc['mods']) + 1;
                    $firstScRow = true;
                ?>
                <?php $__currentLoopData = $sc['mods']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <?php if($firstCompRow): ?>
                            <td class="border b" rowspan="<?php echo e($compRowspan); ?>"><?php echo nl2br(e($block['comp'])); ?></td>
                            <?php $firstCompRow = false; ?>
                        <?php endif; ?>
                        <?php if($firstScRow): ?>
                            <td class="border b" rowspan="<?php echo e($scRowspan); ?>"><?php echo nl2br(e($sc['sc'])); ?></td>
                            <?php $firstScRow = false; ?>
                        <?php endif; ?>
                        <td class="border small"><?php echo e($mod['mod']); ?></td>
                                <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="border center">
                                        <?php echo e(array_key_exists($ua, $b['notes'][$mod['scid']][$mod['modid']] ?? []) ? $b['notes'][$mod['scid']][$mod['modid']][$ua] : '-'); ?>

                                    </td>
                                    <td class="border center">
                                        <?php echo e(array_key_exists($ua, $b['notes'][$mod['scid']][$mod['modid']] ?? []) ? cote($b['notes'][$mod['scid']][$mod['modid']][$ua]) : '-'); ?>

                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <!-- Ligne Total SOUS-COMPÉTENCE -->
                <tr>
                    <td class="border b small" style="background:#f5f5f5;">Total</td>
                    <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="border center b" style="background:#f5f5f5;" colspan="2">
                                    <?php echo e(array_sum(array_column($b['notes'][$mod['scid']] ?? [], $ua))); ?>

                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
        
        <!-- Section statistiques -->
        <div class="stats-section">
            <table class="table-bulletin" style="width:100%; font-size:6pt; border-collapse:collapse;">
                <tr>
                    <td colspan="4" class="b" style="padding:0.5px 1px; background:#e1f7d5; text-align:center; font-size:7pt;">
                        <?php if($isUaUnique): ?>
                            Moyenne séquentielle : <span style="font-weight:bold; font-size:8pt;"><?php echo e($b['moyenne'] ?? ''); ?></span> / 20 <span style="font-weight:bold; color:#007bff;">(<?php echo e(rangOrdinal($b['rangTrimestre'] ?? 0)); ?>)</span>
                        <?php else: ?>
                            Moyenne annuelle : <span style="font-weight:bold; font-size:8pt;"><?php echo e($b['moyenne'] ?? ''); ?></span> / 20 <span style="font-weight:bold; color:#007bff;">(<?php echo e(rangOrdinal($b['rangTrimestre'] ?? 0)); ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="b" style="padding:0.5px 1px;">Résultat annuel</td>
                    <td style="padding:0.5px 1px;">/20</td>
                    <td class="b" style="padding:0.5px 1px;">Profil classe</td>
                    <td style="padding:0.5px 1px;"></td>
                </tr>
                <tr>
                    <td style="padding:0.5px 1px;">Rang élève</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e(rangOrdinal($b['rang'] ?? 0)); ?></td>
                    <td style="padding:0.5px 1px;">Moyenne ≥10</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e($b['profilClasse']['moyenne_10'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td style="padding:0.5px 1px;">Total points</td>
                    <td style="padding:0.5px 1px; text-align:right;">
                        <?php if(is_array($b['totalGeneral'])): ?>
                            <?php echo e(implode(' / ', $b['totalGeneral'])); ?>

                        <?php else: ?>
                            <?php echo e($b['totalGeneral']); ?>

                        <?php endif; ?>
                    </td>
                    <td style="padding:0.5px 1px;">Moyenne classe</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e($b['profilClasse']['moyenne_classe'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td style="padding:0.5px 1px;">Cote</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e(cote($b['moyenne'])); ?></td>
                    <td style="padding:0.5px 1px;">Taux réussite</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e($b['profilClasse']['taux_reussite'] ?? ''); ?>%</td>
                </tr>
                <tr>
                    <td style="padding:0.5px 1px;">Moyenne premier</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e($b['profilClasse']['moyenne_premier'] ?? ''); ?></td>
                    <td style="padding:0.5px 1px;">Moyenne dernier</td>
                    <td style="padding:0.5px 1px; text-align:right;"><?php echo e($b['profilClasse']['moyenne_dernier'] ?? ''); ?></td>
                </tr>
            </table>
            
            
            <?php if(count($uaNums) > 1): ?>
            <table class="table-bulletin" style="width:100%; font-size:6pt; margin-top:1mm; border-collapse:collapse;">
                <tr>
                    <th colspan="<?php echo e(count($uaNums) + 1); ?>" class="center border b" style="background:#f8f9fa;">Moyennes par UA</th>
                </tr>
                <tr>
                    <th class="border b">UA</th>
                    <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="border center b">UA<?php echo e($ua); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <tr>
                    <td class="border b">Moyenne</td>
                    <?php $__currentLoopData = $uaNums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="border center">
                            <?php echo e($b['totalGeneral'][$ua] ?? '0.00'); ?> <span style="color:#007bff; font-weight:bold;">(<?php echo e(rangOrdinal($b['rangsUA'][$ua] ?? 0)); ?>)</span>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </table>
                         <?php endif; ?>
             
             <!-- Section visas -->
             <table class="visa-table" style="width:100%; margin-top: 2mm; font-size: 6pt;">
                 <tr>
                     <td style="text-align: center; padding: 1mm;">Visa du parent</td>
                     <td style="text-align: center; padding: 1mm;">Visa de l'enseignant</td>
                     <td style="text-align: center; padding: 1mm;">Visa du directeur</td>
                 </tr>
                 <tr>
                     <td style="height: 8mm; border-bottom: 1px solid #ccc;"></td>
                     <td style="height: 8mm; border-bottom: 1px solid #ccc;"></td>
                     <td style="height: 8mm; border-bottom: 1px solid #ccc;"></td>
                 </tr>
             </table>
         </div>
     </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/bulletin/annuel-pdf.blade.php ENDPATH**/ ?>