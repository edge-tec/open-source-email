<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
    <div style="text-align:center; margin-bottom:1.5rem">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="EdgeMail Logo" style="height:auto; max-height:120px; max-width:250px;">
    </div>
    <h2 class="card-title">Sign In to EdgeMail</h2>
    <p class="card-desc">Enter your credentials to access your email.</p>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">⚠️ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login.attempt')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-input" name="email" value="<?php echo e(old('email')); ?>" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-input" name="password" required>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <input type="checkbox" name="remember" id="remember" style="accent-color: var(--accent);">
            <label for="remember" style="font-size: 0.85rem; color: var(--text-secondary);">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>