<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Établissements</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo e(route('etablissements.create')); ?>" class="btn btn-primary">Nouvel établissement</a>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Créé le</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $etablissements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($etab->id); ?></td>
                        <td><?php echo e($etab->nom); ?></td>
                        <td><?php echo e($etab->code ?? '-'); ?></td>
                        <td><?php echo e($etab->adresse ?? '-'); ?></td>
                        <td><?php echo e($etab->telephone ?? '-'); ?></td>
                        <td><?php echo e($etab->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-muted">Aucun établissement pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($etablissements->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/etablissements/index.blade.php ENDPATH**/ ?>