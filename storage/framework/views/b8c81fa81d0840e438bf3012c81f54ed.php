<?php $__env->startSection('title', 'Add Mailbox'); ?>
<?php $__env->startSection('content'); ?>
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="<?php echo e(route('admin.mailboxes.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div style="display:flex;align-items:center">
                            <input type="text" class="form-input" name="local_part" placeholder="info" required style="border-top-right-radius:0;border-bottom-right-radius:0;border-right:none">
                            <span style="background:var(--bg2);border:1px solid var(--border);padding:.6rem;color:var(--t2)">@</span>
                            <select name="domain_id" class="form-input" style="border-top-left-radius:0;border-bottom-left-radius:0" required>
                                <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($d->id); ?>"><?php echo e($d->domain); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">Display Name</label><input type="text" class="form-input" name="name" placeholder="John Doe"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-input" name="password" required minlength="8"></div>
                    <div class="form-group"><label class="form-label">Quota (MB)</label><input type="number" class="form-input" name="quota" value="1024" min="1"></div>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create Mailbox</button><a href="<?php echo e(route('admin.mailboxes.index')); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/mailboxes/create.blade.php ENDPATH**/ ?>