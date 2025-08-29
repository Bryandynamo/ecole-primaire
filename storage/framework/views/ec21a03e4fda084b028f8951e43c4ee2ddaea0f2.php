<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-6 fw-bold text-primary">Bulletins scolaires</h1>
        <a href="<?php echo e(route('bulletins.create')); ?>" class="btn btn-success btn-lg"><i class="bi bi-plus-circle"></i> Nouveau bulletin</a>
    </div>
    <div class="alert alert-info mb-4">
        Retrouvez ici tous les bulletins générés pour chaque élève, avec accès rapide à l'impression PDF, la modification et la suppression.<br>
        <strong>Total :</strong> <?php echo e($bulletins->count()); ?> bulletins.
    </div>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <div class="table-responsive shadow-sm rounded">
        <div class="table-responsive">
<table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Session</th>
                    <th>Trimestre</th>
                    <th>Moyenne</th>
                    <th>Décision</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bulletins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bulletin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><span class="btn btn-secondary w-100 mb-2"><?php echo e($bulletin->id); ?></span></td>
                        <td><strong><?php echo e($bulletin->eleve->nom ?? ''); ?> <?php echo e($bulletin->eleve->prenom ?? ''); ?></strong></td>
                        <td><?php echo e($bulletin->classe->nom ?? ''); ?></td>
                        <td><?php echo e($bulletin->session->nom ?? ''); ?></td>
                        <td><span class="badge bg-info"><?php echo e($bulletin->trimestre); ?></span></td>
                        <td><span class="btn btn-primary w-100 mb-2"><?php echo e($bulletin->moyenne); ?></span></td>
                        <td>
                            <?php if(strtolower($bulletin->decision) == 'admis'): ?>
                                <span class="badge bg-success">Admis</span>
                            <?php elseif(strtolower($bulletin->decision) == 'redouble'): ?>
                                <span class="badge bg-danger">Redouble</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?php echo e($bulletin->decision); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?php echo e(route('bulletins.show', $bulletin->id)); ?>" class="btn btn-sm btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                            <a href="<?php echo e(route('bulletins.edit', $bulletin->id)); ?>" class="btn btn-sm btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <a href="<?php echo e(route('bulletins.exportPdf', $bulletin->id)); ?>" class="btn btn-sm btn-outline-success" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <form action="<?php echo e(route('bulletins.destroy', $bulletin->id)); ?>" method="POST" style="display:inline-block;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer ce bulletin ?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
</div>
    </div>
</div>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/bulletins/index.blade.php ENDPATH**/ ?>