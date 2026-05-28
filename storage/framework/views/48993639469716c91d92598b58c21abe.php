<?php $__env->startSection('title', 'Welcome'); ?>

<?php $__env->startSection('steps'); ?>
<div class="steps">
    <div class="step active"><span class="step-num">1</span><span class="step-label">Welcome</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">2</span><span class="step-label">Requirements</span></div>
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
    <h2 class="card-title">Welcome to EdgeMail</h2>
    <p class="card-desc">
        Thank you for choosing EdgeMail — a production-ready, open-source email hosting platform.
        This wizard will guide you through the installation process in just a few minutes.
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.3rem;">Platform</div>
            <div style="font-size: 0.9rem; font-weight: 600;">Laravel 12 + PHP 8.3</div>
        </div>
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.3rem;">Mail Server</div>
            <div style="font-size: 0.9rem; font-weight: 600;">Postfix + Dovecot</div>
        </div>
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.3rem;">Anti-Spam</div>
            <div style="font-size: 0.9rem; font-weight: 600;">Rspamd</div>
        </div>
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 10px; border: 1px solid var(--border-color);">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.3rem;">Docker</div>
            <div style="font-size: 0.9rem; font-weight: 600;">Ready to Deploy</div>
        </div>
    </div>

    <div class="alert alert-info">
        ℹ️ The installer will automatically configure your database, mail server, and admin account.
    </div>

    <div class="btn-row">
        <span></span>
        <a href="<?php echo e(route('installer.requirements')); ?>" class="btn btn-primary">
            Get Started →
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/installer/welcome.blade.php ENDPATH**/ ?>