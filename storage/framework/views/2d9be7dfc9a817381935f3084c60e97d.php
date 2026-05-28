<?php $__env->startSection('title', 'Permissions'); ?>

<?php $__env->startSection('steps'); ?>
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Welcome</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span><span class="step-label">Requirements</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">3</span><span class="step-label">Permissions</span></div>
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
    <h2 class="card-title">Directory Permissions</h2>
    <p class="card-desc">The following directories need to be writable by the web server.</p>

    <ul class="check-list" id="permissionList">
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="check-item">
            <span class="check-icon <?php echo e($perm['writable'] ? 'check-pass' : 'check-fail'); ?>">
                <?php echo e($perm['writable'] ? '✓' : '✗'); ?>

            </span>
            <span class="check-label"><code style="font-size: 0.85rem;"><?php echo e($perm['path']); ?></code></span>
            <span class="check-value"><?php echo e($perm['writable'] ? '0775 ✓' : 'Not writable'); ?></span>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <?php if(!$allWritable): ?>
    <div style="margin-top: 1rem;">
        <button class="btn btn-success" id="fixBtn" onclick="fixPermissions()">
            🔧 Auto-Fix Permissions
        </button>
    </div>
    <?php endif; ?>

    <div class="btn-row">
        <a href="<?php echo e(route('installer.requirements')); ?>" class="btn btn-secondary">← Back</a>
        <a href="<?php echo e(route('installer.database')); ?>" class="btn btn-primary" id="continueBtn">
            Continue →
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
async function fixPermissions() {
    const btn = document.getElementById('fixBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Fixing...';

    try {
        const result = await apiPost('<?php echo e(route("installer.fix-permissions")); ?>', {});
        if (result.success) {
            location.reload();
        }
    } catch (err) {
        btn.innerHTML = '❌ Fix Failed - Try manually';
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/installer/permissions.blade.php ENDPATH**/ ?>