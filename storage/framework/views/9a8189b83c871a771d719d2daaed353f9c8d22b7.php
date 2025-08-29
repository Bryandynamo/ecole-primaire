<?php $__env->startSection('content'); ?>
<div class="container">
    <a href="<?php echo e(route('registre.index')); ?>" class="btn btn-secondary w-100 mb-2">Retour</a>
    <h1>Classes</h1>
    <a href="<?php echo e(route('classes.create')); ?>" class="btn btn-primary w-100 mb-2">Nouvelle classe</a>
    <div class="table-responsive">
<table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Niveau</th>
                <th>Session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($classe->id); ?></td>
                <td><?php echo e($classe->nom); ?></td>
                <td><?php echo e($classe->niveau_id); ?></td>
                <td><?php echo e($classe->session_id); ?></td>
                <td>
                    <a href="<?php echo e(route('classes.edit', $classe)); ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="<?php echo e(route('classes.destroy', $classe)); ?>" method="POST" style="display:inline-block">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette classe ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/classes/index.blade.php ENDPATH**/ ?>