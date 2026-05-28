<?php $__env->startSection('title', 'Add Domain'); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.domains.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group"><label class="form-label">Domain Name</label><input type="text" class="form-input" name="domain" placeholder="example.com" required value="<?php echo e(old('domain')); ?>"></div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" class="form-input" name="description" placeholder="Optional description" value="<?php echo e(old('description')); ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="10" min="1"></div>
                    <div class="form-group"><label class="form-label">Max Aliases</label><input type="number" class="form-input" name="max_aliases" value="50" min="1"></div>
                </div>
                <div class="form-group"><label class="form-label">Max Quota (MB)</label><input type="number" class="form-input" name="max_quota" value="10240" min="100"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create Domain</button><a href="<?php echo e(route('admin.domains.index')); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/domains/create.blade.php ENDPATH**/ ?>