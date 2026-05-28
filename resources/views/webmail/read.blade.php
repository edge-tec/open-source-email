@extends('webmail.layout')
@section('title', $message['subject'])

@section('styles')
<style>
    .read-container{display:flex;flex-direction:column;height:100%;background:var(--bg2)}
    .read-toolbar{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .read-actions{display:flex;gap:.5rem}
    .read-header{padding:1.5rem;border-bottom:1px solid var(--border)}
    .read-subject{font-size:1.4rem;font-weight:700;margin-bottom:1rem;color:var(--t1)}
    .read-meta{display:flex;justify-content:space-between;align-items:center}
    .sender-info{display:flex;align-items:center;gap:1rem}
    .sender-avatar{width:40px;height:40px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:bold;flex-shrink:0;}
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
@endsection

@section('content')
<div class="read-container">
    <div class="read-toolbar">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Back</a>
        
        <div class="read-actions">
            <form method="POST" action="{{ route('webmail.message.reply', $message['uid']) }}">
                @csrf
                <input type="hidden" name="reply_to" value="{{ $message['from'] }}">
                <input type="hidden" name="subject" value="{{ $message['subject'] }}">
                <button class="btn btn-secondary">Reply</button>
            </form>
            <form method="POST" action="{{ route('webmail.message.forward', $message['uid']) }}">
                @csrf
                <input type="hidden" name="to" value="">
                <input type="hidden" name="subject" value="{{ $message['subject'] }}">
                <button class="btn btn-secondary">Forward</button>
            </form>
            
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
    
    <div class="read-body">
        @if($message['body_html'])
            {!! $message['body_html'] !!}
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
