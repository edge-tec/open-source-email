<?php $__env->startSection('title', 'Security'); ?>
<?php $__env->startSection('content'); ?>
    <div class="stat-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Active IP Bans</div><div class="stat-value" style="color:var(--err)"><?php echo e($stats['active_bans']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Failed Logins (Today)</div><div class="stat-value" style="color:var(--warn)"><?php echo e($stats['failed_today']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Failed Logins (Week)</div><div class="stat-value"><?php echo e($stats['failed_week']); ?></div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1.5rem">
        <div class="table-card">
            <div class="table-header"><div class="table-title">Currently Banned IPs</div></div>
            <table>
                <thead><tr><th>IP Address</th><th>Banned Until</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bannedIps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ban): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-family:monospace;color:var(--err)"><?php echo e($ban->ip_address); ?></td>
                        <td><?php echo e($ban->banned_until->diffForHumans()); ?> (<?php echo e($ban->banned_until->format('H:i')); ?>)</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="2" style="text-align:center;color:var(--t3);padding:2rem">No active bans.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <div class="table-header"><div class="table-title">Recent Failed Logins</div></div>
            <table>
                <thead><tr><th>IP</th><th>Email Attempted</th><th>Time</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentFails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-family:monospace;font-size:.8rem"><?php echo e($fail->ip_address); ?></td>
                        <td><?php echo e($fail->email); ?></td>
                        <td style="font-size:.75rem"><?php echo e($fail->created_at->diffForHumans()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--t3);padding:2rem">No recent failures.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="table-card" style="grid-column: 1 / -1;">
            <div class="table-header"><div class="table-title">SSL Configuration</div></div>
            <div style="padding: 1.5rem">
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;">1. Auto-Generate Self-Signed Certificate</h4>
                    <p style="font-size: 0.85rem; color: var(--t2); margin-bottom: 1rem;">Click below to instantly generate a self-signed SSL certificate for development or internal use.</p>
                    <form method="POST" action="<?php echo e(route('admin.security.ssl.self-signed')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will restart the mail server. Continue?')">Generate Self-Signed SSL</button>
                    </form>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 2rem 0;">

                <div>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;">2. Install Custom / Let's Encrypt SSL</h4>
                    <p style="font-size: 0.85rem; color: var(--t2); margin-bottom: 1rem;">Paste your fullchain certificate and private key below to install a custom SSL certificate.</p>
                    <form method="POST" action="<?php echo e(route('admin.security.ssl.custom')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Certificate (CRT/Fullchain)</label>
                            <textarea name="certificate" class="form-input" rows="5" placeholder="-----BEGIN CERTIFICATE-----" required></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Private Key (KEY)</label>
                            <textarea name="private_key" class="form-input" rows="5" placeholder="-----BEGIN PRIVATE KEY-----" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will restart the mail server. Continue?')">Install SSL</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/security/index.blade.php ENDPATH**/ ?>