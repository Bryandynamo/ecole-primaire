<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Gestion Scolaire'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    
    
    <meta name="theme-color" content="#0275d8">
</head>
<body class="bg-light" style="min-height:100vh;"
<?php if(isset($evaluation) && is_object($evaluation) && isset($classe) && is_object($classe)): ?> data-evaluation-id="<?php echo e($evaluation->id); ?>" data-classe-id="<?php echo e($classe->id); ?>" <?php endif; ?>>
    <div class="d-flex" style="min-height:100vh;">
        <?php if(!isset($pdfMode) && !isset($exportMode)): ?>
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="bg-primary text-white flex-shrink-0 p-3" style="width:260px;min-height:100vh;">
            <div class="mb-4 text-center">
                <span class="fs-4 fw-bold sidebar-title">École Primaire</span>
                <div class="small sidebar-label">Gestion des Bulletins</div>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
          

<li class="nav-item mb-1"><a href="/registre" class="nav-link text-white"><i class="bi bi-clipboard-data"></i> <span class="sidebar-label">Registre</span></a></li>

            </ul>
            <hr>
            <div class="d-flex align-items-center mt-4 sidebar-user">
    <i id="adminIcon" class="bi bi-person-circle fs-3 me-2" style="cursor:pointer;"></i>
    <div>
        <div class="fw-bold"><?php if(auth()->guard()->check()): ?> <?php echo e(Auth::user()->name); ?> <?php else: ?> Admin <?php endif; ?></div>
        <div class="small"><?php if(auth()->guard()->check()): ?> Enseignant <?php else: ?> Administrateur <?php endif; ?></div>
    </div>
</div>
        </nav>
        <?php endif; ?>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <?php if(!isset($pdfMode) && !isset($exportMode)): ?>
            <header class="bg-white border-bottom shadow-sm p-3 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fs-5 fw-bold text-primary"><?php echo $__env->yieldContent('title', 'Gestion des bulletins scolaires'); ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted small">
                        Session courante : <span class="fw-bold"><?php if(session('session_label')): ?> <?php echo e(session('session_label')); ?> <?php else: ?> Aucune <?php endif; ?></span>
                    </div>
                    <?php if(auth()->guard()->check()): ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Se déconnecter
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </header>
            <?php endif; ?>
            <!-- Toast de notification -->
            <div aria-live="polite" aria-atomic="true" class="position-relative">
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
                    <?php if(session('success')): ?>
                        <div class="toast align-items-center text-bg-success border-0 show" id="toast-success" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?php echo e(session('success')); ?>

                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(session('error')): ?>
                        <div class="toast align-items-center text-bg-danger border-0 show" id="toast-error" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?php echo e(session('error')); ?>

                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <main class="container-fluid px-4">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" onerror="(function(){var s=document.createElement('script');s.src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';s.integrity='';s.crossOrigin='anonymous';document.head.appendChild(s);})();"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <?php echo $__env->yieldContent('scripts'); ?>
        <?php echo $__env->yieldPushContent('scripts'); ?>

        
        <?php
            $isCouverture = request()->routeIs('couverture.show') || request()->routeIs('couverture.show_new');
        ?>
        <?php if (! ($isCouverture)): ?>
            
            
        <?php endif; ?>
        <script>
            // Vérifier le chargement de jQuery
            if (typeof jQuery === 'undefined') {
                console.warn('jQuery n\'est pas chargé — tentative de fallback tardif');
                var s2 = document.createElement('script');
                s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
                document.head.appendChild(s2);
            } else {
                console.log('jQuery version:', jQuery.fn.jquery);
                // Tester jQuery avec un simple selecteur
                console.log('Test jQuery:', $('body').length);
            }
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toastSuccess = document.getElementById('toast-success');
                if (toastSuccess) {
                    var bsToast = new bootstrap.Toast(toastSuccess, { delay: 5000 });
                    bsToast.show();
                }

                var toastError = document.getElementById('toast-error');
                if (toastError) {
                    var bsToast = new bootstrap.Toast(toastError, { delay: 5000 });
                    bsToast.show();
                }
            });
        </script>
    </div>
</div>
<style>
    #sidebarMenu {
        transition: width 0.3s cubic-bezier(.4,0,.2,1);
        width: 260px;
        overflow-x: hidden;
        min-height: 100vh;
    }
    .sidebar-mini {
        width: 60px !important;
    }
    #sidebarMenu .sidebar-label,
    #sidebarMenu .sidebar-extra,
    #sidebarMenu .sidebar-title,
    #sidebarMenu .sidebar-user {
        transition: opacity 0.2s;
        opacity: 1;
        white-space: nowrap;
    }
    .sidebar-mini .sidebar-label,
    .sidebar-mini .sidebar-extra,
    .sidebar-mini .sidebar-title,
    .sidebar-mini .sidebar-user {
        opacity: 0;
        pointer-events: none;
    }
    #sidebarMenu .nav-link {
        display: flex;
        align-items: center;
    }
    #sidebarMenu .nav-link i {
        margin-right: 12px;
        font-size: 1.3em;
    }
    @media (max-width: 768px) {
        #sidebarMenu {
            position: absolute;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1040;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('sidebarMenu');
        var adminIcon = document.getElementById('adminIcon');
        if (adminIcon && sidebar) {
            adminIcon.addEventListener('click', function () {
                sidebar.classList.toggle('sidebar-mini');
            });
        }
    });
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/service-worker.js').then(function(registration) {
        // Registration successful
      }, function(err) {
        // Registration failed
      });
    });
  }
</script>
</body>
</html>
<?php /**PATH C:\Users\User Dell\3D Objects\bulletin\webapp-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>