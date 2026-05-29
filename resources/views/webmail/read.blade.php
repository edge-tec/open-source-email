@extends('webmail.layout')
@section('title', $message['subject'])

@section('styles')
<style>
    .read-container{display:flex;flex-direction:column;height:100%;background:var(--bg2)}
    .read-toolbar{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .read-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
    .read-header{padding:1.5rem;border-bottom:1px solid var(--border)}
    .read-subject{font-size:1.4rem;font-weight:700;margin-bottom:1rem;color:var(--t1)}
    .read-meta{display:flex;justify-content:space-between;align-items:center}
    .sender-info{display:flex;align-items:center;gap:1rem}
    .sender-avatar{width:40px;height:40px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:bold;flex-shrink:0;}
    .sender-name{font-weight:600;color:var(--t1);font-size:.95rem}
    .sender-email{font-size:.8rem;color:var(--t3)}
    .read-date{font-size:.8rem;color:var(--t2);flex-shrink:0}
    
    .read-body{padding:2rem 1.5rem;flex:1;overflow-y:auto;font-size:.95rem;line-height:1.6;color:var(--t1)}
    .read-body img{max-width:100%;height:auto}
    .read-body blockquote{border-left:3px solid var(--border);padding-left:1rem;margin:1rem 0;color:var(--t2)}
    
    .attachments-area{padding:1rem 1.5rem;border-top:1px solid var(--border);background:var(--bg)}
    .attachment-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:var(--bg2);border:1px solid var(--border);border-radius:6px;font-size:.8rem;color:var(--t2);margin-right:.5rem;text-decoration:none;margin-bottom:.5rem}
    .attachment-badge:hover{background:var(--bg-hover)}

    /* ===== RESPONSIVE: Mobile ===== */
    @media(max-width:768px){
        .read-toolbar{flex-direction:column;gap:.75rem;align-items:stretch;padding:1rem}
        .read-actions{justify-content:center}
        .read-header{padding:1rem}
        .read-subject{font-size:1.1rem;margin-bottom:.75rem}
        .read-meta{flex-direction:column;align-items:flex-start;gap:.75rem}
        .sender-info{gap:.75rem}
        .sender-avatar{width:34px;height:34px;font-size:.9rem}
        .read-date{align-self:flex-end}
        .read-body{padding:1rem}
        .attachments-area{padding:.75rem 1rem}
        .attachment-badge{font-size:.75rem;padding:.4rem .6rem}
    }

    /* ===== RESPONSIVE: Small Phone ===== */
    @media(max-width:480px){
        .read-toolbar{padding:.75rem}
        .read-actions .btn{padding:.35rem .6rem;font-size:.78rem}
        .read-header{padding:.75rem}
        .read-subject{font-size:1rem}
        .sender-name{font-size:.85rem}
        .sender-email{font-size:.72rem}
        .read-body{padding:.75rem;font-size:.88rem}
    }
</style>
@endsection

@section('content')
<div class="read-container">
    <div class="read-toolbar">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Back</a>
        
        <div class="read-actions">
            <a href="{{ route('webmail.compose', ['reply_uid' => $message['uid'], 'folder' => $folder, 'to' => $message['from'], 'subject' => 'Re: ' . $message['subject']]) }}" class="btn btn-secondary">Reply</a>
            <a href="{{ route('webmail.compose', ['forward_uid' => $message['uid'], 'folder' => $folder, 'subject' => 'Fwd: ' . $message['subject']]) }}" class="btn btn-secondary">Forward</a>
            
            <div style="width:1px;background:var(--border);margin:0 .5rem"></div>
            
            <form method="POST" action="{{ route('webmail.message.toggle-read', $message['uid']) }}">
                @csrf 
                <input type="hidden" name="source_folder" value="{{ $folder ?? 'INBOX' }}">
                <button class="btn-icon" title="Toggle Unread">✉️</button>
            </form>
            <form method="POST" action="{{ route('webmail.message.move', $message['uid']) }}">
                @csrf 
                <input type="hidden" name="source_folder" value="{{ $folder ?? 'INBOX' }}">
                <input type="hidden" name="folder" value="Junk">
                <button class="btn-icon" style="color:var(--warn)" title="Mark as Spam">🚫</button>
            </form>
            <form method="POST" action="{{ route('webmail.message.destroy', $message['uid']) }}" onsubmit="return confirm('Delete message?')">
                @csrf @method('DELETE') 
                <input type="hidden" name="source_folder" value="{{ $folder ?? 'INBOX' }}">
                <button class="btn-icon" style="color:var(--err)" title="Delete">🗑</button>
            </form>
        </div>
    </div>
    
    <div class="read-header">
        <h1 class="read-subject">{{ $message['subject'] }}</h1>
        <div class="read-meta">
            <div class="sender-info">
                <div class="sender-avatar">{{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $message['from']), 0, 1) ?: '@') }}</div>
                <div>
                    <div class="sender-name">{{ explode('<', $message['from'])[0] }}</div>
                    <div class="sender-email">{{ $message['from'] }}</div>
                    @if($message['to'])<div class="sender-email" style="margin-top:.2rem">To: {{ $message['to'] }}</div>@endif
                </div>
            </div>
            <div class="read-date">{{ date('M d, Y h:i A', strtotime($message['date'])) }}</div>
        </div>
    </div>
    
    <div class="read-body" @if($message['body_html']) style="padding:0; overflow:hidden; position:relative; background:#fff;" @endif>
        @if($message['body_html'])
            <iframe srcdoc="{{ $message['body_html'] }}" class="email-iframe" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin" onload="makeIframeResponsive(this)" style="width:100%; height:100%; border:none; position:absolute; top:0; left:0; display:block;"></iframe>
        @else
            <div style="white-space:pre-wrap;font-family:monospace">{{ $message['body_text'] }}</div>
        @endif
    </div>
      
    @if(count($message['attachments']) > 0)
    <div class="attachments-area">
        <div style="font-size:.8rem;font-weight:600;margin-bottom:.5rem;color:var(--t3)">Attachments ({{ count($message['attachments']) }})</div>
        <div>
            @foreach($message['attachments'] as $att)
                <a href="#" class="attachment-badge">📎 {{ $att['filename'] }} ({{ round($att['size']/1024) }} KB)</a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function makeIframeResponsive(iframe) {
    try {
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        
        // Add viewport meta if not exists
        if (!doc.querySelector('meta[name="viewport"]')) {
            var meta = doc.createElement('meta');
            meta.name = "viewport";
            meta.content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
            doc.head.appendChild(meta);
        }

        // Add responsive CSS to ensure tables and images scale down
        var style = doc.createElement('style');
        style.innerHTML = `
            * {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            body { 
                margin: 0 !important; 
                padding: 15px !important; 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important; 
                overflow-x: hidden !important; /* Prevent horizontal scrolling */
                background-color: #ffffff !important;
                word-wrap: break-word !important;
            }
            img { 
                max-width: 100% !important; 
                height: auto !important; 
                object-fit: contain !important;
            }
            /* Make tables responsive */
            table { 
                width: 100% !important; 
                max-width: 100% !important;
                table-layout: fixed !important;
            }
            table, tr, td, th { 
                max-width: 100% !important; 
                word-wrap: break-word !important;
            }
            @media only screen and (max-width: 768px) {
                body { padding: 10px !important; }
                table, tbody, tr, th, td {
                    display: block !important;
                    width: 100% !important;
                    height: auto !important;
                }
            }
        `;
        doc.head.appendChild(style);

        // Fix all links to open in a new tab
        var links = doc.querySelectorAll('a');
        links.forEach(function(link) {
            link.setAttribute('target', '_blank');
        });
    } catch (e) {
        console.error("Could not inject responsive styles into iframe", e);
    }
}
</script>
@endsection
