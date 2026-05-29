<?php $__env->startSection('title', 'Edit ' . $domain->domain); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.domains.update', $domain)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="form-group"><label class="form-label">Domain</label><input type="text" class="form-input" name="domain" value="<?php echo e($domain->domain); ?>" required></div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" class="form-input" name="description" value="<?php echo e($domain->description); ?>"></div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" <?php echo e($domain->status==='active'?'selected':''); ?>>Active</option><option value="inactive" <?php echo e($domain->status==='inactive'?'selected':''); ?>>Inactive</option><option value="pending" <?php echo e($domain->status==='pending'?'selected':''); ?>>Pending</option></select></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="<?php echo e($domain->max_mailboxes); ?>"></div>
                    <div class="form-group"><label class="form-label">Max Aliases</label><input type="number" class="form-input" name="max_aliases" value="<?php echo e($domain->max_aliases); ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Catch-All Email</label><input type="email" class="form-input" name="catch_all" value="<?php echo e($domain->catch_all); ?>" placeholder="catchall{{ $domain->domain }}"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button class="btn btn-primary">Update</button><a href="<?php echo e(route('admin.domains.show', $domain)); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <form method="POST" action="<?php echo e(route('admin.domains.destroy', $domain)); ?>" style="margin-top:1rem" onsubmit="return confirm('Delete this domain and all its mailboxes?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger">Delete Domain</button></form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/domains/edit.blade.php ENDPATH**/ ?>