<?php $__env->startSection('title', 'Mailboxes'); ?>
<?php $__env->startSection('actions'); ?><a href="<?php echo e(route('admin.mailboxes.create')); ?>" class="btn btn-primary btn-sm">+ Add Mailbox</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search email..." value="<?php echo e(request('search')); ?>">
                <select name="domain_id" class="form-input" style="width:200px">
                    <option value="">All Domains</option>
                    <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($d->id); ?>" <?php echo e(request('domain_id') == $d->id ? 'selected' : ''); ?>><?php echo e($d->domain); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Email</th><th>Name</th><th>Domain</th><th>Quota</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $mailboxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight:600"><?php echo e($mb->email); ?></td>
                    <td><?php echo e($mb->name ?? '—'); ?></td>
                    <td><?php echo e($mb->domain->domain); ?></td>
                    <td>
                        <div style="font-size:.7rem;margin-bottom:.2rem"><?php echo e($mb->getQuotaPercentage()); ?>% of <?php echo e($mb->quota); ?>MB</div>
                        <div style="width:100%;background:var(--border);height:4px;border-radius:2px"><div style="width:<?php echo e(min(100, $mb->getQuotaPercentage())); ?>%;background:<?php echo e($mb->getQuotaPercentage() > 90 ? 'var(--err)' : 'var(--accent)'); ?>;height:100%;border-radius:2px"></div></div>
                    </td>
                    <td><span class="badge <?php echo e($mb->status === 'active' ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($mb->status); ?></span></td>
                    <td><a href="<?php echo e(route('admin.mailboxes.edit', $mb)); ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No mailboxes yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($mailboxes->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/mailboxes/index.blade.php ENDPATH**/ ?>