<?php $__env->startSection('title', 'Edit User'); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input" name="name" value="<?php echo e($user->name); ?>" required></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" name="email" value="<?php echo e($user->email); ?>" required></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="user" <?php echo e($user->role=='user'?'selected':''); ?>>User</option>
                        <option value="reseller" <?php echo e($user->role=='reseller'?'selected':''); ?>>Reseller</option>
                        <?php if(auth()->user()->isSuperAdmin()): ?>
                        <option value="admin" <?php echo e($user->role=='admin'?'selected':''); ?>>Admin</option>
                        <option value="super_admin" <?php echo e($user->role=='super_admin'?'selected':''); ?>>Super Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" <?php echo e($user->status==='active'?'selected':''); ?>>Active</option><option value="inactive" <?php echo e($user->status==='inactive'?'selected':''); ?>>Inactive</option></select></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Domains</label><input type="number" class="form-input" name="max_domains" value="<?php echo e($user->max_domains); ?>" min="1"></div>
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="<?php echo e($user->max_mailboxes); ?>" min="1"></div>
                </div>
                <div class="form-group"><label class="form-label">Change Password (leave blank to keep current)</label><input type="password" class="form-input" name="password" minlength="8"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Update User</button><a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <?php if(!$user->isSuperAdmin() || \App\Models\User::role('super_admin')->count() > 1): ?>
        <form method="POST" action="<?php echo e(route('admin.users.destroy', $user)); ?>" style="margin-top:1rem" onsubmit="return confirm('Delete this user? This will also delete their domains and mailboxes.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger">Delete User</button></form>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>