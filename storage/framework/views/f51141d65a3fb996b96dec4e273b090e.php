<?php $__env->startSection('title', 'Email Logs'); ?>
<?php $__env->startSection('content'); ?>
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search emails..." value="<?php echo e(request('search')); ?>">
                <select name="direction" class="form-input" style="width:130px">
                    <option value="">All Types</option>
                    <option value="inbound" <?php echo e(request('direction')=='inbound'?'selected':''); ?>>Inbound</option>
                    <option value="outbound" <?php echo e(request('direction')=='outbound'?'selected':''); ?>>Outbound</option>
                </select>
                <select name="status" class="form-input" style="width:130px">
                    <option value="">All Statuses</option>
                    <option value="delivered" <?php echo e(request('status')=='delivered'?'selected':''); ?>>Delivered</option>
                    <option value="sent" <?php echo e(request('status')=='sent'?'selected':''); ?>>Sent</option>
                    <option value="bounced" <?php echo e(request('status')=='bounced'?'selected':''); ?>>Bounced</option>
                    <option value="rejected" <?php echo e(request('status')=='rejected'?'selected':''); ?>>Rejected</option>
                    <option value="deferred" <?php echo e(request('status')=='deferred'?'selected':''); ?>>Deferred</option>
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Time</th><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Status</th><th>Spam Score</th><th>Details</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-size:.75rem"><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                    <td><span class="badge <?php echo e($log->direction === 'inbound' ? 'badge-info' : 'badge-ok'); ?>"><?php echo e(substr($log->direction, 0, 3)); ?></span></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($log->sender, 20)); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($log->recipient, 20)); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($log->subject, 30)); ?></td>
                    <td><span class="badge <?php echo e(in_array($log->status, ['sent', 'delivered']) ? 'badge-ok' : (in_array($log->status, ['bounced', 'rejected']) ? 'badge-err' : 'badge-warn')); ?>"><?php echo e($log->status); ?></span></td>
                    <td><?php echo e($log->direction === 'inbound' ? $log->spam_score : '—'); ?></td>
                    <td><a href="<?php echo e(route('admin.logs.show', $log)); ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--t3);padding:2rem">No logs found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/logs/index.blade.php ENDPATH**/ ?>