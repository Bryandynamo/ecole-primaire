<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un utilisateur</title>
    <style>
        body {font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f5f7fb; margin:0;}
        .wrap {min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;}
        .card {background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(18,38,63,.08); width:100%; max-width:520px; padding:28px 28px 22px;}
        h1 {font-size:20px; margin:0 0 12px; color:#111827;}
        label {display:block; font-size:13px; color:#374151; margin:10px 0 6px;}
        input[type="text"], input[type="email"], input[type="password"], select {width:100%; padding:12px 14px; border:1px solid #e5e7eb; border-radius:8px; outline:none; font-size:14px}
        .btn {background:#2563eb; color:#fff; border:none; padding:12px 16px; border-radius:8px; font-weight:600; cursor:pointer; margin-top:14px}
        .btn:hover {background:#1e40af}
        .error {background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:10px}
        .status {background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:10px}
        .row {display:flex; gap:12px}
        a.link {color:#2563eb; text-decoration:none}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Créer un utilisateur</h1>

        <?php if(session('status')): ?>
            <div class="status"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="error">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('users.store')); ?>">
            <?php echo csrf_field(); ?>
            <label for="invite_code">Code d'invitation</label>
            <input type="text" id="invite_code" name="invite_code" value="<?php echo e(old('invite_code')); ?>" required>

            <label for="name">Nom</label>
            <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="username">

            <div class="row">
                <div style="flex:1">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                <div style="flex:1">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <hr style="margin:16px 0; border:none; border-top:1px solid #e5e7eb">
            <h2 style="font-size:16px; margin:0 0 8px; color:#111827;">Informations Enseignant</h2>

            <div class="row">
                <div style="flex:1">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo e(old('nom')); ?>" required>
                </div>
                <div style="flex:1">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo e(old('prenom')); ?>" required>
                </div>
            </div>

            <label for="matricule">Matricule</label>
            <input type="text" id="matricule" name="matricule" value="<?php echo e(old('matricule')); ?>" required>

            <label for="etablissement_id">Établissement</label>
            <select id="etablissement_id" name="etablissement_id" required>
                <option value="">-- Sélectionner --</option>
                <?php $__currentLoopData = ($etablissements ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($e->id); ?>" <?php if(old('etablissement_id') == $e->id): echo 'selected'; endif; ?>><?php echo e($e->nom); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <label for="classe_id">Classe</label>
            <select id="classe_id" name="classe_id" required>
                <option value="">-- Sélectionner --</option>
                <?php $__currentLoopData = ($classes ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" data-etab="<?php echo e($c->etablissement_id); ?>" <?php if(old('classe_id') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->nom); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="btn">Créer</button>
        </form>
        <div style="margin-top:12px">
            <a class="link" href="<?php echo e(url('/')); ?>">Retour à l'accueil</a>
        </div>
    </div>
</div>
<script>
    (function(){
        const etabSelect = document.getElementById('etablissement_id');
        const classeSelect = document.getElementById('classe_id');
        function filterClasses(){
            const etabId = etabSelect.value;
            const selected = classeSelect.value;
            let hasAny = false;
            Array.from(classeSelect.options).forEach(opt => {
                if (!opt.value) return; // skip placeholder
                const match = !etabId || opt.getAttribute('data-etab') === etabId;
                opt.hidden = !match;
                if (match) hasAny = true;
            });
            // If current selection does not belong to etab, reset
            const selOpt = classeSelect.selectedOptions[0];
            if (selOpt && selOpt.hidden) classeSelect.value = '';
            // If no classes available, keep placeholder
            if (!hasAny) classeSelect.value = '';
        }
        if (etabSelect && classeSelect){
            etabSelect.addEventListener('change', filterClasses);
            // Run on load to reflect old inputs
            filterClasses();
        }
    })();
</script>
</body>
</html>
<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/users/create.blade.php ENDPATH**/ ?>