<?php $__env->startSection('title', 'Aliases'); ?>
<?php $__env->startSection('actions'); ?><a href="<?php echo e(route('admin.aliases.create')); ?>" class="btn btn-primary btn-sm">+ Add Alias</a><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem"><input type="text" name="search" class="form-input" style="width:250px" placeholder="Search aliases..." value="<?php echo e(request('search')); ?>"><button class="btn btn-secondary btn-sm">Search</button></form>
        </div>
        <table>
            <thead><tr><th>Source</th><th>Destination</th><th>Domain</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $aliases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alias): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight:600"><?php echo e($alias->source); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($alias->destination, 40)); ?></td>
                    <td><?php echo e($alias->domain->domain); ?></td>
                    <td><span class="badge <?php echo e($alias->status === 'active' ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($alias->status); ?></span></td>
                    <td><a href="<?php echo e(route('admin.aliases.edit', $alias)); ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--t3);padding:2rem">No aliases yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($aliases->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/aliases/index.blade.php ENDPATH**/ ?>