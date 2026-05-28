<?php $__env->startSection('title', isset($currentFolder) ? $currentFolder : (isset($searchQuery) ? 'Search Results' : 'Inbox')); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .mail-list{height:100%;overflow-y:auto;background:var(--bg2)}
    .mail-item{display:grid;grid-template-columns:40px 200px 1fr 100px;align-items:center;padding:.75rem 1.5rem;border-bottom:1px solid var(--border);cursor:pointer;text-decoration:none;color:var(--t1);transition:background .15s}
    .mail-item:hover{background:var(--bg-hover)}
    .mail-item.unread{font-weight:600;background:var(--bg)}
    .mail-item.unread:hover{background:var(--bg-hover)}
    .mail-sender{font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:1rem;color:var(--t1)}
    .mail-subject{font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--t2)}
    .mail-item.unread .mail-subject{color:var(--t1)}
    .mail-date{font-size:.75rem;color:var(--t3);text-align:right}
    .mail-avatar{width:28px;height:28px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:bold}
    .list-header{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg2)}
    .list-title{font-size:1.1rem;font-weight:600}
    
    .toolbar{display:flex;gap:.5rem}
    .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--t3)}
    .empty-icon{font-size:3rem;margin-bottom:1rem;opacity:.5}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <form id="bulkForm" method="POST" action="<?php echo e(route('webmail.bulk')); ?>" style="display:flex;flex-direction:column;height:100%">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="folder" value="<?php echo e($currentFolder ?? 'INBOX'); ?>">
        <div class="list-header">
            <div class="list-title">
                <?php if(isset($searchQuery)): ?>
                    Search: "<?php echo e($searchQuery); ?>"
                <?php else: ?>
                    <?php echo e(isset($currentFolder) ? $currentFolder : 'Inbox'); ?>

                <?php endif; ?>
            </div>
            <div class="toolbar" style="align-items:center">
                <input type="text" name="q" form="searchForm" class="form-control" style="padding:.4rem;width:200px" placeholder="Search emails..." value="<?php echo e($searchQuery ?? ''); ?>" onkeydown="if(event.key === 'Enter') { event.preventDefault(); document.getElementById('searchForm').submit(); }">
                <button type="submit" form="searchForm" class="btn btn-secondary" style="padding:.4rem .75rem">Search</button>

                <?php if(isset($currentFolder) && strcasecmp($currentFolder, 'Trash') === 0): ?>
                    <button type="submit" form="emptyTrashForm" class="btn btn-secondary" style="color:var(--err)">Empty Trash</button>
                <?php endif; ?>
                <select name="action" class="form-control" style="width:auto;padding:.4rem;display:none" id="bulkActions" onchange="if(this.value) document.getElementById('bulkForm').submit()">
                    <option value="">Bulk Actions...</option>
                    <option value="read">Mark as Read</option>
                    <option value="unread">Mark as Unread</option>
                    <option value="move_junk">Move to Spam</option>
                    <option value="move_trash">Move to Trash</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <button type="button" class="btn-icon" title="Refresh" onclick="window.location.reload()">↻</button>
            </div>
        </div>
        
        <?php if(session('success')): ?>
            <div style="padding:1rem 1.5rem 0"><div class="alert alert-success"><?php echo e(session('success')); ?></div></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div style="padding:1rem 1.5rem 0"><div class="alert alert-error"><?php echo e($errors->first()); ?></div></div>
        <?php endif; ?>

        <div class="mail-list">
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mail-item <?php echo e(!$msg['seen'] ? 'unread' : ''); ?>" onclick="if(event.target.type !== 'checkbox') window.location.href='<?php echo e(route('webmail.message.show', $msg['uid'])); ?>?folder=<?php echo e(urlencode($currentFolder ?? 'INBOX')); ?>'">
                    <div style="display:flex;align-items:center;gap:.75rem">
                        <input type="checkbox" name="uids[]" value="<?php echo e($msg['uid']); ?>" class="mail-checkbox" style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer">
                        <div class="mail-avatar"><?php echo e(strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $msg['from']), 0, 1) ?: '@')); ?></div>
                    </div>
                    <div class="mail-sender" title="<?php echo e($msg['from']); ?>"><?php echo e(explode('<', $msg['from'])[0]); ?></div>
                    <div class="mail-subject"><?php echo e($msg['subject']); ?></div>
                    <div class="mail-date"><?php echo e(date('M d', strtotime($msg['date']))); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3 style="font-weight:500;margin-bottom:.5rem">No messages found</h3>
                    <p style="font-size:.85rem">Your <?php echo e(isset($currentFolder) ? strtolower($currentFolder) : 'inbox'); ?> is empty.</p>
                </div>
            <?php endif; ?>
        </div>
    </form>
    
    <?php if(isset($currentFolder) && strcasecmp($currentFolder, 'Trash') === 0): ?>
        <form id="emptyTrashForm" action="<?php echo e(route('webmail.trash.empty')); ?>" method="POST" style="display:none" onsubmit="return confirm('Are you sure you want to empty the Trash? This cannot be undone.')">
            <?php echo csrf_field(); ?>
        </form>
    <?php endif; ?>
    
    <form id="searchForm" action="<?php echo e(route('webmail.search')); ?>" method="GET" style="display:none"></form>
    
    <script>
        document.querySelectorAll('.mail-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const anyChecked = document.querySelectorAll('.mail-checkbox:checked').length > 0;
                document.getElementById('bulkActions').style.display = anyChecked ? 'inline-block' : 'none';
            });
        });
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('webmail.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/webmail/inbox.blade.php ENDPATH**/ ?>