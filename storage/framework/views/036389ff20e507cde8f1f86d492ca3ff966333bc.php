<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Créer un nouvel établissement</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Erreurs :</strong>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('etablissements.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom de l'établissement</label>
            <input type="text" name="nom" id="nom" class="form-control" value="<?php echo e(old('nom')); ?>" required>
        </div>

        <div class="mb-3">
            <label for="code" class="form-label">Code (optionnel, unique)</label>
            <input type="text" name="code" id="code" class="form-control" value="<?php echo e(old('code')); ?>">
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse (optionnel)</label>
            <input type="text" name="adresse" id="adresse" class="form-control" value="<?php echo e(old('adresse')); ?>">
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone (optionnel)</label>
            <input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo e(old('telephone')); ?>">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="<?php echo e(route('etablissements.index')); ?>" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/etablissements/create.blade.php ENDPATH**/ ?>