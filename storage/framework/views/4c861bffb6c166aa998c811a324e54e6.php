<?php $__env->startSection('title', 'Add Alias'); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.aliases.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group"><label class="form-label">Domain</label><select name="domain_id" class="form-input" required><?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->domain); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="form-group"><label class="form-label">Alias Address (Source)</label><input type="text" class="form-input" name="source" placeholder="alias@domain.com" required></div>
                <div class="form-group"><label class="form-label">Destination Addresses (Comma separated)</label><textarea class="form-input" name="destination" required rows="3" placeholder="user@domain.com, external@gmail.com"></textarea></div>
                <div class="form-group"><label class="form-label">Comment / Notes</label><input type="text" class="form-input" name="comment"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create Alias</button><a href="<?php echo e(route('admin.aliases.index')); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/aliases/create.blade.php ENDPATH**/ ?>