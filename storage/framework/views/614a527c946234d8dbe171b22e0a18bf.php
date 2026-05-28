<?php $__env->startSection('title', 'Storage Usage'); ?>
<?php $__env->startSection('content'); ?>
    <div class="stat-grid" style="margin-bottom:1.5rem">
        <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="stat-card">
            <div class="stat-label"><?php echo e($d->domain); ?></div>
            <div class="stat-value" style="font-size:1.2rem"><?php echo e(round($d->total_used)); ?> / <?php echo e($d->total_quota); ?> MB</div>
            <div style="width:100%;background:var(--border);height:4px;border-radius:2px;margin-top:.5rem">
                <?php $pct = $d->total_quota > 0 ? min(100, ($d->total_used / $d->total_quota) * 100) : 0; ?>
                <div style="width:<?php echo e($pct); ?>%;background:<?php echo e($pct > 90 ? 'var(--err)' : 'var(--accent)'); ?>;height:100%;border-radius:2px"></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="table-card">
        <div class="table-header"><div class="table-title">Largest Mailboxes</div></div>
        <table>
            <thead><tr><th>Mailbox</th><th>Domain</th><th>Messages</th><th>Used Space</th><th>Quota</th><th>Usage %</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $storage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight:600"><?php echo e($s->mailbox->email); ?></td>
                    <td><?php echo e($s->domain->domain); ?></td>
                    <td><?php echo e(number_format($s->message_count)); ?></td>
                    <td><?php echo e(round($s->used_bytes / 1048576, 2)); ?> MB</td>
                    <td><?php echo e(round($s->quota_bytes / 1048576, 2)); ?> MB</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:100px;background:var(--border);height:6px;border-radius:3px"><div style="width:<?php echo e(min(100, $s->percentage_used)); ?>%;background:<?php echo e($s->percentage_used > 90 ? 'var(--err)' : 'var(--accent)'); ?>;height:100%;border-radius:3px"></div></div>
                            <span style="font-size:.75rem;color:<?php echo e($s->percentage_used > 90 ? 'var(--err)' : 'var(--t2)'); ?>"><?php echo e(round($s->percentage_used)); ?>%</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No storage data calculated yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($storage->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/storage/index.blade.php ENDPATH**/ ?>