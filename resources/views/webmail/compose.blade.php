@extends('webmail.layout')
@section('title', 'Compose')

@section('styles')
<style>
    .compose-container{display:flex;flex-direction:column;height:100%;background:var(--bg2)}
    .compose-header{padding:1.25rem 2rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg2)}
    .compose-header h2{font-size:1.25rem;font-weight:700;letter-spacing:-0.02em}
    .compose-form{display:flex;flex-direction:column;flex:1;overflow:hidden}
    
    .compose-fields{padding:0 2rem}
    .field-row{display:flex;align-items:center;border-bottom:1px solid var(--border);padding:.75rem 0;transition:border-color .2s}
    .field-row:focus-within{border-bottom-color:var(--accent)}
    .field-label{width:70px;color:var(--t3);font-size:.9rem;font-weight:500;flex-shrink:0}
    .field-input{flex:1;border:none;background:transparent;outline:none;font-size:1rem;color:var(--t1)}
    .field-input::placeholder{color:var(--t3);opacity:.7}
    
    .editor-wrapper{flex:1;display:flex;flex-direction:column;overflow:hidden;padding:0 2rem}
    #editor{flex:1;font-family:'Inter',sans-serif;font-size:1rem;color:var(--t1);line-height:1.6}
    
    /* Quill Customization */
    .ql-toolbar.ql-snow{border:none !important;border-bottom:1px solid var(--border) !important;padding:.75rem 0 !important;background:transparent;display:flex;flex-wrap:wrap;gap:4px}
    .ql-container.ql-snow{border:none !important;font-family:'Inter',sans-serif;font-size:1rem}
    .ql-editor{padding:1.5rem 0 !important}
    .ql-editor.ql-blank::before{left:0 !important;color:var(--t3);font-style:normal}
    .ql-snow .ql-stroke{stroke:var(--t2)}
    .ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill{fill:var(--t2)}
    .ql-toolbar.ql-snow .ql-picker{color:var(--t2)}
    
    /* Compose Footer */
    .compose-footer{padding:1rem 2rem 1.5rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);background:var(--bg2)}
    .footer-actions{display:flex;align-items:center;gap:1rem}
    
    /* Buttons */
    .btn-send{background:var(--accent);color:white;padding:.6rem 1.5rem;border-radius:20px;font-weight:600;font-size:.95rem;border:none;cursor:pointer;transition:transform .1s, background .2s;display:inline-flex;align-items:center;gap:.5rem}
    .btn-send:hover{background:var(--accent2);transform:translateY(-1px)}
    .btn-send:active{transform:translateY(0)}
    .btn-discard{background:transparent;color:var(--t3);padding:.6rem 1rem;border-radius:20px;font-weight:500;font-size:.9rem;border:none;cursor:pointer;transition:background .2s}
    .btn-discard:hover{background:var(--bg)}
    
    /* Attachment Button */
    .attachment-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--bg);color:var(--t2);border-radius:8px;font-size:.85rem;font-weight:500;cursor:pointer;transition:background .2s,color .2s;border:1px solid var(--border)}
    .attachment-btn:hover{background:var(--bg3);color:var(--t1)}
    #attachment-list{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;font-size:.8rem;color:var(--t2)}
    .file-badge{background:var(--bg3);padding:.25rem .5rem;border-radius:4px;border:1px solid var(--border)}

    /* ===== RESPONSIVE ===== */
    @media(max-width:768px){
        .compose-header, .compose-fields, .editor-wrapper, .compose-footer{padding-left:1rem;padding-right:1rem}
        .compose-header{flex-direction:column;gap:.75rem;align-items:flex-start}
        .compose-header > div{width:100%;display:flex;justify-content:space-between}
        .compose-footer{flex-direction:column-reverse;gap:1rem;align-items:stretch}
        .footer-actions{justify-content:space-between}
        .btn-send{width:100%;justify-content:center}
    }
</style>
@endsection

@section('content')
<div class="compose-container">
    <form id="composeForm" method="POST" action="{{ route('webmail.send') }}" class="compose-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="body" id="bodyInput">
        
        <div class="compose-header">
            <h2>New Message</h2>
            <div>
                <a href="{{ route('webmail.inbox') }}" class="btn-discard">Discard</a>
                <button type="button" class="btn-discard" onclick="submitForm('draft')">Save Draft</button>
            </div>
        </div>
        
        @if($errors->any())
            <div style="padding:1rem 2rem 0"><div class="alert alert-error" style="color:var(--err)">{{ $errors->first() }}</div></div>
        @endif
        
        <div class="compose-fields">
            <div class="field-row">
                <div class="field-label">To</div>
                <input type="email" class="field-input" name="to" value="{{ $replyTo['to'] ?? '' }}" placeholder="recipient@example.com" required autofocus autocomplete="off">
            </div>
            <div class="field-row">
                <div class="field-label">Subject</div>
                <input type="text" class="field-input" name="subject" value="{{ $replyTo['subject'] ?? '' }}" placeholder="What's this about?" required autocomplete="off">
            </div>
        </div>
        
        <div class="editor-wrapper">
            <div id="editor">{!! $replyTo['body'] ?? '' !!}{!! $mailbox->signature ? '<br><br>--<br>' . nl2br(e($mailbox->signature)) : '' !!}</div>
        </div>
        
        <div class="compose-footer">
            <div>
                <label class="attachment-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    Attach Files
                    <input type="file" name="attachments[]" multiple style="display:none" onchange="updateFileList(this)">
                </label>
                <div id="attachment-list"></div>
            </div>
            <div class="footer-actions">
                <button type="button" class="btn-send" onclick="submitForm('send')">
                    Send Message
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
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
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    function submitForm(action) {
        var html = quill.root.innerHTML;
        // Don't send empty content
        if(quill.getText().trim().length === 0 && !quill.container.querySelector('img')) {
            html = '';
        }
        document.getElementById('bodyInput').value = html;
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        document.getElementById('composeForm').appendChild(actionInput);
        document.getElementById('composeForm').submit();
    }
    
    function updateFileList(input) {
        const list = document.getElementById('attachment-list');
        list.innerHTML = '';
        Array.from(input.files).forEach(file => {
            const badge = document.createElement('div');
            badge.className = 'file-badge';
            badge.textContent = file.name;
            list.appendChild(badge);
        });
    }
</script>
@endsection
