<?php $__env->startSection('title', 'Edit ' . $mailbox->email); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.mailboxes.update', $mailbox)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="form-group"><label class="form-label">Email</label><input type="text" class="form-input" value="<?php echo e($mailbox->email); ?>" disabled></div>
                <div class="form-group"><label class="form-label">Display Name</label><input type="text" class="form-input" name="name" value="<?php echo e($mailbox->name); ?>"></div>
                <div class="form-group"><label class="form-label">New Password (leave blank to keep current)</label><input type="password" class="form-input" name="password" minlength="8"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Quota (MB)</label><input type="number" class="form-input" name="quota" value="<?php echo e($mailbox->quota); ?>"></div>
                    <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" <?php echo e($mailbox->status==='active'?'selected':''); ?>>Active</option><option value="inactive" <?php echo e($mailbox->status==='inactive'?'selected':''); ?>>Inactive</option></select></div>
                </div>
                <div style="margin-top:1rem">
                    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;font-size:.85rem"><input type="checkbox" name="is_catchall" value="1" <?php echo e($mailbox->is_catchall ? 'checked' : ''); ?> style="accent-color:var(--accent)"> Set as Domain Catch-all</label>
                    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;font-size:.85rem"><input type="checkbox" name="send_only" value="1" <?php echo e($mailbox->send_only ? 'checked' : ''); ?> style="accent-color:var(--accent)"> Send Only (Cannot receive)</label>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1.5rem"><button type="submit" class="btn btn-primary">Update Mailbox</button><a href="<?php echo e(route('admin.mailboxes.index')); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <form method="POST" action="<?php echo e(route('admin.mailboxes.destroy', $mailbox)); ?>" style="margin-top:1rem" onsubmit="return confirm('Delete this mailbox? All emails will be lost.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger">Delete Mailbox</button></form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/mailboxes/edit.blade.php ENDPATH**/ ?>