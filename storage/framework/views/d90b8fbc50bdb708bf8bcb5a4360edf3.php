<?php $__env->startSection('title', 'Requirements'); ?>

<?php $__env->startSection('steps'); ?>
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Welcome</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">2</span><span class="step-label">Requirements</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">3</span><span class="step-label">Permissions</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">4</span><span class="step-label">Database</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">5</span><span class="step-label">Mail</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">6</span><span class="step-label">Admin</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h2 class="card-title">Server Requirements</h2>
    <p class="card-desc">Checking that your server meets all requirements for EdgeMail.</p>

    
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">PHP Version</h3>
    <ul class="check-list" style="margin-bottom: 1.5rem;">
        <li class="check-item">
            <span class="check-icon <?php echo e($checks['php']['satisfied'] ? 'check-pass' : 'check-fail'); ?>">
                <?php echo e($checks['php']['satisfied'] ? '✓' : '✗'); ?>

            </span>
            <span class="check-label"><?php echo e($checks['php']['name']); ?></span>
            <span class="check-value"><?php echo e($checks['php']['current']); ?> (requires <?php echo e($checks['php']['required']); ?>)</span>
        </li>
    </ul>

    
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">PHP Extensions</h3>
    <ul class="check-list" style="margin-bottom: 1.5rem;">
        <?php $__currentLoopData = $checks['extensions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ext): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="check-item">
            <span class="check-icon <?php echo e($ext['loaded'] ? 'check-pass' : ($ext['critical'] ? 'check-fail' : 'check-warn')); ?>">
                <?php echo e($ext['loaded'] ? '✓' : '✗'); ?>

            </span>
            <span class="check-label"><?php echo e($ext['name']); ?></span>
            <span class="check-value"><?php echo e($ext['loaded'] ? 'Loaded' : ($ext['critical'] ? 'Required' : 'Optional')); ?></span>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    
    <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem;">Server Settings</h3>
    <ul class="check-list">
        <?php $__currentLoopData = $checks['server']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="check-item">
            <span class="check-icon <?php echo e($req['satisfied'] ? 'check-pass' : 'check-warn'); ?>">
                <?php echo e($req['satisfied'] ? '✓' : '!'); ?>

            </span>
            <span class="check-label"><?php echo e($req['name']); ?></span>
            <span class="check-value"><?php echo e($req['current']); ?> (min: <?php echo e($req['required']); ?>)</span>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <?php if(!$allMet): ?>
    <div class="alert alert-danger" style="margin-top: 1rem;">
        ⚠️ Some critical requirements are not met. Please install the missing PHP extensions before continuing.
    </div>
    <?php endif; ?>

    <div class="btn-row">
        <a href="<?php echo e(route('installer.welcome')); ?>" class="btn btn-secondary">← Back</a>
        <a href="<?php echo e(route('installer.permissions')); ?>" class="btn btn-primary <?php echo e(!$allMet ? 'disabled' : ''); ?>" <?php if(!$allMet): ?> onclick="return false;" <?php endif; ?>>
            Continue →
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/installer/requirements.blade.php ENDPATH**/ ?>