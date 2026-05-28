@extends('admin.layout')
@section('title', 'Log Details')
@section('content')
    <div style="max-width:800px">
        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom:1rem">← Back to Logs</a>
        <div class="table-card" style="padding:1.5rem">
            <h3 style="font-size:1rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:.5rem">Email Transaction Details</h3>
            <div style="display:grid;grid-template-columns:120px 1fr;gap:1rem;margin-bottom:.5rem">
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Message ID</div><div style="font-family:monospace">{{ $log->message_id ?? 'N/A' }}</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Time</div><div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Direction</div><div><span class="badge {{ $log->direction === 'inbound' ? 'badge-info' : 'badge-ok' }}">{{ $log->direction }}</span></div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Status</div><div><span class="badge {{ in_array($log->status, ['sent', 'delivered']) ? 'badge-ok' : (in_array($log->status, ['bounced', 'rejected']) ? 'badge-err' : 'badge-warn') }}">{{ $log->status }}</span></div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">From</div><div>{{ $log->sender }}</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">To</div><div>{{ $log->recipient }}</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Subject</div><div>{{ $log->subject }}</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Size</div><div>{{ round($log->size / 1024, 2) }} KB</div>
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Client IP</div><div>{{ $log->client_ip ?? 'N/A' }}</div>
                @if($log->direction === 'inbound')
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Spam Score</div><div>{{ $log->spam_score }} {{ $log->is_spam ? '(Marked as Spam)' : '' }}</div>
                @endif
                <div style="color:var(--t3);font-weight:600;font-size:.85rem">Error / DSN</div><div style="color:var(--err)">{{ $log->error_message ?? 'None' }} {{ $log->dsn_code ? "({$log->dsn_code})" : '' }}</div>
            </div>
        </div>
    </div>
@endsection
