<?php $__env->startSection('title', 'Spam Filters'); ?>
<?php $__env->startSection('content'); ?>
    <div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem">
        <div class="table-card" style="padding:1.5rem;align-self:start">
            <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">Add Global Filter</h3>
            <form method="POST" action="<?php echo e(route('admin.spam.store-filter')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group"><label class="form-label">Domain (Optional)</label><select name="domain_id" class="form-input"><option value="">All Domains (Global)</option><?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->domain); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="form-group"><label class="form-label">Filter Type</label><select name="filter_type" class="form-input"><option value="blacklist">Blacklist</option><option value="whitelist">Whitelist</option></select></div>
                <div class="form-group"><label class="form-label">Value (Email or Domain)</label><input type="text" name="value" class="form-input" placeholder="*@spammer.com" required></div>
                <div class="form-group"><label class="form-label">Action</label><select name="action" class="form-input"><option value="reject">Reject</option><option value="mark">Mark as Spam</option><option value="accept">Accept (Whitelist)</option></select></div>
                <button class="btn btn-primary" style="width:100%">Add Filter</button>
            </form>
        </div>
        <div class="table-card">
            <table>
                <thead><tr><th>Scope</th><th>Type</th><th>Value</th><th>Action</th><th>Status</th><th>Delete</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($f->domain_id ? $f->domain->domain : 'Global'); ?></td>
                        <td><span class="badge <?php echo e($f->filter_type === 'whitelist' ? 'badge-ok' : 'badge-err'); ?>"><?php echo e($f->filter_type); ?></span></td>
                        <td style="font-family:monospace"><?php echo e($f->value); ?></td>
                        <td><?php echo e($f->action); ?></td>
                        <td><span class="badge <?php echo e($f->status === 'active' ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($f->status); ?></span></td>
                        <td><form method="POST" action="<?php echo e(route('admin.spam.destroy-filter', $f)); ?>" onsubmit="return confirm('Remove filter?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger btn-sm">🗑</button></form></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No custom spam filters set.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="pagination"><?php echo e($filters->links()); ?></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/spam/index.blade.php ENDPATH**/ ?>