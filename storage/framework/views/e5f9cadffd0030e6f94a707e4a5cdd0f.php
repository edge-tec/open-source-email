<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('actions'); ?><a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary btn-sm">+ Add User</a><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search users..." value="<?php echo e(request('search')); ?>">
                <select name="role" class="form-input" style="width:150px">
                    <option value="">All Roles</option>
                    <option value="super_admin" <?php echo e(request('role')=='super_admin'?'selected':''); ?>>Super Admin</option>
                    <option value="admin" <?php echo e(request('role')=='admin'?'selected':''); ?>>Admin</option>
                    <option value="reseller" <?php echo e(request('role')=='reseller'?'selected':''); ?>>Reseller</option>
                    <option value="user" <?php echo e(request('role')=='user'?'selected':''); ?>>User</option>
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Name</th><th>Role</th><th>Domains</th><th>Mailboxes</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><div style="font-weight:600"><?php echo e($user->name); ?></div><div style="font-size:.75rem;color:var(--t3)"><?php echo e($user->email); ?></div></td>
                    <td><span class="badge badge-info"><?php echo e($user->role); ?></span></td>
                    <td><?php echo e($user->domains_count); ?>/<?php echo e($user->max_domains); ?></td>
                    <td><?php echo e($user->mailboxes_count); ?>/<?php echo e($user->max_mailboxes); ?></td>
                    <td><span class="badge <?php echo e($user->status === 'active' ? 'badge-ok' : 'badge-warn'); ?>"><?php echo e($user->status); ?></span></td>
                    <td><a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($users->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>