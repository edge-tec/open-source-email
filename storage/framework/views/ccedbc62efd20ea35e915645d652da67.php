<?php $__env->startSection('title', 'Mail Queue'); ?>
<?php $__env->startSection('content'); ?>
    <div class="stat-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Queued</div><div class="stat-value" style="color:var(--info)"><?php echo e($stats['queued']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value" style="color:var(--ok)"><?php echo e($stats['active']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Deferred</div><div class="stat-value" style="color:var(--warn)"><?php echo e($stats['deferred']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Bounced</div><div class="stat-value" style="color:var(--err)"><?php echo e($stats['bounced']); ?></div></div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>ID</th><th>Sender</th><th>Recipient</th><th>Status</th><th>Attempts</th><th>Next Retry</th><th>Error</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $queue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-family:monospace;font-size:.75rem"><?php echo e(substr($q->queue_id, 0, 8)); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($q->sender, 20)); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($q->recipient, 20)); ?></td>
                    <td><span class="badge <?php echo e($q->status === 'queued' ? 'badge-info' : ($q->status === 'deferred' ? 'badge-warn' : 'badge-err')); ?>"><?php echo e($q->status); ?></span></td>
                    <td><?php echo e($q->attempts); ?></td>
                    <td style="font-size:.75rem"><?php echo e($q->next_retry_at ? $q->next_retry_at->diffForHumans() : '—'); ?></td>
                    <td style="color:var(--err);font-size:.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo e($q->error_message); ?>"><?php echo e($q->error_message ?? '—'); ?></td>
                    <td style="display:flex;gap:.25rem">
                        <form method="POST" action="<?php echo e(route('admin.queue.retry', $q->id)); ?>"><?php echo csrf_field(); ?><button class="btn btn-secondary btn-sm" title="Retry">↻</button></form>
                        <form method="POST" action="<?php echo e(route('admin.queue.destroy', $q->id)); ?>" onsubmit="return confirm('Remove message from queue? It will not be sent.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger btn-sm" title="Delete">🗑</button></form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--t3);padding:2rem">Queue is empty.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($queue->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/queue/index.blade.php ENDPATH**/ ?>