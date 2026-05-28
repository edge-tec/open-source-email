<?php $__env->startSection('title', $domain->domain); ?>
<?php $__env->startSection('content'); ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Mailboxes</div><div class="stat-value"><?php echo e($domain->mailboxes->count()); ?>/<?php echo e($domain->max_mailboxes); ?></div></div>
        <div class="stat-card"><div class="stat-label">Aliases</div><div class="stat-value"><?php echo e($domain->aliases->count()); ?>/<?php echo e($domain->max_aliases); ?></div></div>
    </div>

    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">DNS Records</h3>
        <div style="background:var(--bg);padding:1rem;border-radius:8px;font-family:monospace;font-size:.8rem;color:var(--t2);margin-bottom:.75rem;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">MX Record:</strong> <?php echo e($domain->domain); ?>. IN MX 10 <?php echo e(config('edgemail.hostname')); ?>.</div>
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">SPF:</strong> <?php echo e($domain->spf_record ?? 'v=spf1 mx a ~all'); ?></div>
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">DMARC:</strong> <?php echo e($domain->dmarc_record ?? 'Not configured'); ?></div>
            <?php if($domain->dkim_enabled && $domain->dkimKeys->count()): ?>
            <div style="word-break:break-all;"><strong style="color:var(--t1)">DKIM:</strong> <?php echo e($domain->dkimKeys->first()->dns_record ?? 'Key generated'); ?></div>
            <?php endif; ?>
        </div>
        <?php if(!$domain->dkim_enabled): ?>
        <form method="POST" action="<?php echo e(route('admin.domains.generate-dkim', $domain)); ?>"><?php echo csrf_field(); ?><button class="btn btn-primary btn-sm">Generate DKIM Key</button></form>
        <?php endif; ?>
    </div>

    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">SSL Configuration (SNI)</h3>
        <p style="font-size:.85rem;color:var(--t2);margin-bottom:1rem">Upload a custom SSL certificate for <strong>mail.<?php echo e($domain->domain); ?></strong> to enable secure connections for this domain.</p>
        <form method="POST" action="<?php echo e(route('admin.domains.ssl', $domain)); ?>">
            <?php echo csrf_field(); ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1rem;margin-bottom:1rem">
                <div class="form-group">
                    <label class="form-label">Certificate (CRT/Fullchain)</label>
                    <textarea name="certificate" class="form-input" rows="4" placeholder="-----BEGIN CERTIFICATE-----" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Private Key (KEY)</label>
                    <textarea name="private_key" class="form-input" rows="4" placeholder="-----BEGIN PRIVATE KEY-----" required></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('This will restart the mail server. Continue?')">Install SSL for <?php echo e($domain->domain); ?></button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header"><div class="table-title">Mailboxes</div></div>
        <table>
            <thead><tr><th>Email</th><th>Name</th><th>Quota</th><th>Status</th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $domain->mailboxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td><?php echo e($mb->email); ?></td><td><?php echo e($mb->name); ?></td><td><?php echo e($mb->getQuotaPercentage()); ?>%</td><td><span class="badge badge-ok"><?php echo e($mb->status); ?></span></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/domains/show.blade.php ENDPATH**/ ?>