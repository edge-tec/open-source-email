<?php $__env->startSection('title', 'Domains'); ?>
<?php $__env->startSection('actions'); ?><a href="<?php echo e(route('admin.domains.create')); ?>" class="btn btn-primary btn-sm">+ Add Domain</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem"><input type="text" name="search" class="form-input" style="width:250px" placeholder="Search domains..." value="<?php echo e(request('search')); ?>"><button class="btn btn-secondary btn-sm">Search</button></form>
        </div>
        <table>
            <thead><tr><th>Domain</th><th>Owner</th><th>Mailboxes</th><th>Aliases</th><th>Status</th><th>DKIM</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight:600"><?php echo e($domain->domain); ?></td>
                    <td><?php echo e($domain->user->name ?? '—'); ?></td>
                    <td><?php echo e($domain->mailboxes_count); ?>/<?php echo e($domain->max_mailboxes); ?></td>
                    <td><?php echo e($domain->aliases_count); ?>/<?php echo e($domain->max_aliases); ?></td>
                    <td><span class="badge <?php echo e($domain->status === 'active' ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($domain->status); ?></span></td>
                    <td><span class="badge <?php echo e($domain->dkim_enabled ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($domain->dkim_enabled ? 'On' : 'Off'); ?></span></td>
                    <td><a href="<?php echo e(route('admin.domains.show', $domain)); ?>" class="btn btn-secondary btn-sm">View</a> <a href="<?php echo e(route('admin.domains.edit', $domain)); ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--t3);padding:2rem">No domains yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($domains->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/domains/index.blade.php ENDPATH**/ ?>