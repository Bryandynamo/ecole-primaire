<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Sélection du registre de notes</h1>
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <div class="table-responsive">
                <label for="session_id" class="form-label">Session académique</label>
            </div>
            <select name="session_id" id="session_id" class="form-select w-100" required <?php if(isset($sessions) && $sessions->count() === 1): ?> disabled <?php endif; ?>>
                <option value="" disabled>Choisir une session...</option>
                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($session->id); ?>" <?php if(isset($selectedSession) && $selectedSession == $session->id): ?> selected <?php endif; ?>><?php echo e($session->nom); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if(isset($sessions) && $sessions->count() === 1): ?>
                <input type="hidden" name="session_id" value="<?php echo e($sessions->first()->id); ?>">
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <div class="table-responsive">
                <label for="classe_id" class="form-label">Classe</label>
            </div>
            <select name="classe_id" id="classe_id" class="form-select w-100" required <?php if(isset($classes) && $classes->count() === 1): ?> disabled <?php endif; ?>>
                <option value="" disabled>Choisir une classe...</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($classe->id); ?>" <?php if(isset($selectedClasse) && $selectedClasse == $classe->id): ?> selected <?php endif; ?>><?php echo e($classe->nom); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if(isset($classes) && $classes->count() === 1): ?>
                <input type="hidden" name="classe_id" value="<?php echo e($classes->first()->id); ?>">
            <?php endif; ?>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-secondary w-100 mb-2">Charger les évaluations</button>
        </div>
        <?php if(isset($evaluations) && $evaluations->count()): ?>
        <div class="col-md-6">
            <div class="table-responsive">
                <label for="evaluation_id" class="form-label">Évaluation</label>
            </div>
            <select name="evaluation_id" id="evaluation_id" class="form-select w-100" required>
                <option value="" disabled selected>Choisir une évaluation...</option>
                <?php $__currentLoopData = $evaluations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($eval->numero_eval); ?>">
                        <?php echo e($eval->trimestre); ?> - UA <?php echo e($eval->numero_eval); ?> (<?php echo e($eval->date_eval); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-12 mt-3">
            <button type="button" class="btn btn-primary w-100 mb-2" id="go-registre">Accéder au registre</button>
        </div>
        <?php endif; ?>
    </form>
</div>

<?php if(session('error')): ?>
    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('go-registre');
    const sessionSelect = document.getElementById('session_id');
    const classeSelect = document.getElementById('classe_id');
    const evalSelect = document.getElementById('evaluation_id');

    function updateBtnState() {
        if (btn) {
            btn.disabled = !(sessionSelect && sessionSelect.value && classeSelect && classeSelect.value && evalSelect && evalSelect.value);
        }
    }
    if (sessionSelect) sessionSelect.addEventListener('change', updateBtnState);
    if (classeSelect) classeSelect.addEventListener('change', updateBtnState);
    if (evalSelect) evalSelect.addEventListener('change', updateBtnState);
    updateBtnState();

    if(btn) {
        btn.addEventListener('click', function() {
            const session_id = sessionSelect.value;
            const classe_id = classeSelect.value;
            const evaluation_num = evalSelect.value;
            if(session_id && classe_id && evaluation_num) {
                window.location.href = `/registre/${session_id}/${classe_id}/${evaluation_num}`;
            } else {
                alert('Veuillez sélectionner une session, une classe et une évaluation avant de continuer.');
            }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/registre/index.blade.php ENDPATH**/ ?>