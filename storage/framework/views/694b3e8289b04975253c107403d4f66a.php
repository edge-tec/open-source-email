<?php $__env->startSection('title', $message['subject']); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .read-container{display:flex;flex-direction:column;height:100%;background:var(--bg2)}
    .read-toolbar{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .read-actions{display:flex;gap:.5rem}
    .read-header{padding:1.5rem;border-bottom:1px solid var(--border)}
    .read-subject{font-size:1.4rem;font-weight:700;margin-bottom:1rem;color:var(--t1)}
    .read-meta{display:flex;justify-content:space-between;align-items:center}
    .sender-info{display:flex;align-items:center;gap:1rem}
    .sender-avatar{width:40px;height:40px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:bold}
    .sender-name{font-weight:600;color:var(--t1);font-size:.95rem}
    .sender-email{font-size:.8rem;color:var(--t3)}
    .read-date{font-size:.8rem;color:var(--t2)}
    
    .read-body{padding:2rem 1.5rem;flex:1;overflow-y:auto;font-size:.95rem;line-height:1.6;color:var(--t1)}
    .read-body img{max-width:100%;height:auto}
    .read-body blockquote{border-left:3px solid var(--border);padding-left:1rem;margin:1rem 0;color:var(--t2)}
    
    .attachments-area{padding:1rem 1.5rem;border-top:1px solid var(--border);background:var(--bg)}
    .attachment-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:var(--bg2);border:1px solid var(--border);border-radius:6px;font-size:.8rem;color:var(--t2);margin-right:.5rem;text-decoration:none}
    .attachment-badge:hover{background:var(--bg-hover)}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="read-container">
    <div class="read-toolbar">
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">← Back</a>
        
        <div class="read-actions">
            <form method="POST" action="<?php echo e(route('webmail.message.reply', $message['uid'])); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="reply_to" value="<?php echo e($message['from']); ?>">
                <input type="hidden" name="subject" value="<?php echo e($message['subject']); ?>">
                <button class="btn btn-secondary">Reply</button>
            </form>
            <form method="POST" action="<?php echo e(route('webmail.message.forward', $message['uid'])); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="to" value="">
                <input type="hidden" name="subject" value="<?php echo e($message['subject']); ?>">
                <button class="btn btn-secondary">Forward</button>
            </form>
            
            <div style="width:1px;background:var(--border);margin:0 .5rem"></div>
            
            <form method="POST" action="<?php echo e(route('webmail.message.toggle-read', $message['uid'])); ?>">
                <?php echo csrf_field(); ?> 
                <input type="hidden" name="source_folder" value="<?php echo e($folder ?? 'INBOX'); ?>">
                <button class="btn-icon" title="Toggle Unread">✉️</button>
            </form>
            <form method="POST" action="<?php echo e(route('webmail.message.move', $message['uid'])); ?>">
                <?php echo csrf_field(); ?> 
                <input type="hidden" name="source_folder" value="<?php echo e($folder ?? 'INBOX'); ?>">
                <input type="hidden" name="folder" value="Junk">
                <button class="btn-icon" style="color:var(--warn)" title="Mark as Spam">🚫</button>
            </form>
            <form method="POST" action="<?php echo e(route('webmail.message.destroy', $message['uid'])); ?>" onsubmit="return confirm('Delete message?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?> 
                <input type="hidden" name="source_folder" value="<?php echo e($folder ?? 'INBOX'); ?>">
                <button class="btn-icon" style="color:var(--err)" title="Delete">🗑</button>
            </form>
        </div>
    </div>
    
    <div class="read-header">
        <h1 class="read-subject"><?php echo e($message['subject']); ?></h1>
        <div class="read-meta">
            <div class="sender-info">
                <div class="sender-avatar"><?php echo e(strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $message['from']), 0, 1) ?: '@')); ?></div>
                <div>
                    <div class="sender-name"><?php echo e(explode('<', $message['from'])[0]); ?></div>
                    <div class="sender-email"><?php echo e($message['from']); ?></div>
                    <?php if($message['to']): ?><div class="sender-email" style="margin-top:.2rem">To: <?php echo e($message['to']); ?></div><?php endif; ?>
                </div>
            </div>
            <div class="read-date"><?php echo e(date('M d, Y h:i A', strtotime($message['date']))); ?></div>
        </div>
    </div>
    
    <div class="read-body">
        <?php if($message['body_html']): ?>
            <?php echo $message['body_html']; ?>

        <?php else: ?>
            <div style="white-space:pre-wrap;font-family:monospace"><?php echo e($message['body_text']); ?></div>
        <?php endif; ?>
    </div>
    
    <?php if(count($message['attachments']) > 0): ?>
    <div class="attachments-area">
        <div style="font-size:.8rem;font-weight:600;margin-bottom:.5rem;color:var(--t3)">Attachments (<?php echo e(count($message['attachments'])); ?>)</div>
        <div>
            <?php $__currentLoopData = $message['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#" class="attachment-badge">📎 <?php echo e($att['filename']); ?> (<?php echo e(round($att['size']/1024)); ?> KB)</a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('webmail.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/webmail/read.blade.php ENDPATH**/ ?>