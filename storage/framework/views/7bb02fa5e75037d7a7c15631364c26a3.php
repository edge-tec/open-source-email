<?php $__env->startSection('title', 'Compose'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .compose-container{display:flex;flex-direction:column;height:100%;background:var(--bg2)}
    .compose-header{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .compose-form{display:flex;flex-direction:column;flex:1;overflow:hidden}
    .compose-fields{padding:1rem 1.5rem;border-bottom:1px solid var(--border)}
    .field-row{display:flex;align-items:center;border-bottom:1px solid var(--border);padding:.5rem 0}
    .field-row:last-child{border-bottom:none}
    .field-label{width:60px;color:var(--t3);font-size:.85rem}
    .field-input{flex:1;border:none;background:transparent;outline:none;font-size:.9rem;color:var(--t1)}
    
    .editor-container{flex:1;display:flex;flex-direction:column;overflow:hidden}
    #editor{flex:1;font-family:'Inter',sans-serif;font-size:.95rem;color:var(--t1)}
    .ql-toolbar{border-left:none !important;border-right:none !important;border-top:none !important;background:var(--bg)}
    .ql-container{border:none !important;font-family:'Inter',sans-serif;font-size:.95rem}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="compose-container">
    <div class="compose-header">
        <h2 style="font-size:1.1rem;font-weight:600">New Message</h2>
        <div style="display:flex;gap:.5rem">
            <a href="<?php echo e(route('webmail.inbox')); ?>" class="btn btn-secondary">Discard</a>
            <button type="button" class="btn btn-secondary" onclick="submitForm('draft')">Save as Draft</button>
            <button type="button" class="btn btn-primary" onclick="submitForm('send')">Send Message</button>
        </div>
    </div>
    
    <?php if($errors->any()): ?>
        <div style="padding:1rem 1.5rem 0"><div class="alert alert-error"><?php echo e($errors->first()); ?></div></div>
    <?php endif; ?>
    
    <form id="composeForm" method="POST" action="<?php echo e(route('webmail.send')); ?>" class="compose-form" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="body" id="bodyInput">
        <div class="compose-fields">
            <div class="field-row">
                <div class="field-label">To:</div>
                <input type="text" class="field-input" name="to" value="<?php echo e($replyTo['to'] ?? ''); ?>" placeholder="recipient@example.com" required autofocus>
            </div>
            <div class="field-row">
                <div class="field-label">Subject:</div>
                <input type="text" class="field-input" name="subject" value="<?php echo e($replyTo['subject'] ?? ''); ?>" placeholder="Message subject" required>
            </div>
            <div class="field-row" style="border-bottom:none;padding-bottom:0">
                <input type="file" name="attachments[]" multiple style="font-size:.8rem;color:var(--t2)">
            </div>
        </div>
        
        <div class="editor-container">
            <div id="editor"><?php echo $mailbox->signature ? '<br><br>--<br>' . nl2br(e($mailbox->signature)) : ''; ?></div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Write your message here...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    function submitForm(action) {
        var html = quill.root.innerHTML;
        document.getElementById('bodyInput').value = html;
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        document.getElementById('composeForm').appendChild(actionInput);
        document.getElementById('composeForm').submit();
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('webmail.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/webmail/compose.blade.php ENDPATH**/ ?>