<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Enseignants</h1>
    <a href="<?php echo e(route('enseignants.create')); ?>" class="btn btn-primary w-100 mb-2">Nouvel enseignant</a>
    <div class="table-responsive">
<table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Matricule</th>
                <th>Classe</th>
                <th>Session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $enseignants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enseignant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($enseignant->id); ?></td>
                <td><?php echo e($enseignant->nom); ?></td>
                <td><?php echo e($enseignant->prenom); ?></td>
                <td><?php echo e($enseignant->matricule); ?></td>
                <td><?php echo e($enseignant->classe_id); ?></td>
                <td><?php echo e($enseignant->session_id); ?></td>
                <td>
                    <a href="<?php echo e(route('enseignants.edit', $enseignant)); ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="<?php echo e(route('enseignants.destroy', $enseignant)); ?>" method="POST" style="display:inline-block">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet enseignant ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/enseignants/index.blade.php ENDPATH**/ ?>