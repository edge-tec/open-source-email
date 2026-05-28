<?php $__env->startSection('title', 'Mail Configuration'); ?>

<?php $__env->startSection('steps'); ?>
<div class="steps">
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step completed"><span class="step-num">✓</span></div>
    <div class="step-line completed"></div>
    <div class="step active"><span class="step-num">5</span><span class="step-label">Mail</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">6</span><span class="step-label">Admin</span></div>
    <div class="step-line"></div>
    <div class="step"><span class="step-num">7</span><span class="step-label">Install</span></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h2 class="card-title">Mail Server Configuration</h2>
    <p class="card-desc">Configure your primary mail domain and server settings.</p>

    <form id="mailForm" action="<?php echo e(route('installer.admin')); ?>" method="GET">
        <div class="form-group">
            <label class="form-label">Primary Mail Domain</label>
            <input type="text" class="form-input" name="domain" id="domain" placeholder="example.com" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">The main domain for receiving and sending emails.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Mail Server Hostname</label>
            <input type="text" class="form-input" name="hostname" id="hostname" placeholder="mail.example.com" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Must match your server's DNS A record and MX record.</small>
        </div>

        <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin: 1.5rem 0 0.75rem;">SMTP Settings</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">SMTP Host</label>
                <input type="text" class="form-input" name="smtp_host" value="127.0.0.1">
            </div>
            <div class="form-group">
                <label class="form-label">SMTP Port</label>
                <input type="text" class="form-input" name="smtp_port" value="587">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">SMTP Encryption</label>
            <select class="form-input" name="smtp_encryption">
                <option value="tls" selected>TLS (Recommended)</option>
                <option value="ssl">SSL</option>
                <option value="none">None</option>
            </select>
        </div>

        <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin: 1.5rem 0 0.75rem;">IMAP Settings</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">IMAP Host</label>
                <input type="text" class="form-input" name="imap_host" value="127.0.0.1">
            </div>
            <div class="form-group">
                <label class="form-label">IMAP Port</label>
                <input type="text" class="form-input" name="imap_port" value="993">
            </div>
        </div>

        <div class="btn-row">
            <a href="<?php echo e(route('installer.database')); ?>" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.getElementById('domain').addEventListener('input', function() {
    document.getElementById('hostname').value = 'mail.' + this.value;
});

document.getElementById('mailForm').addEventListener('submit', function() {
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const existing = JSON.parse(sessionStorage.getItem('edgemail_db') || '{}');
    sessionStorage.setItem('edgemail_config', JSON.stringify({...existing, ...data}));
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/installer/mail-config.blade.php ENDPATH**/ ?>