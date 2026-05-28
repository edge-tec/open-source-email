@extends('admin.layout')
@section('title', 'Storage Usage')
@section('content')
    <div class="stat-grid" style="margin-bottom:1.5rem">
        @foreach($domains as $d)
        <div class="stat-card">
            <div class="stat-label">{{ $d->domain }}</div>
            <div class="stat-value" style="font-size:1.2rem">{{ round($d->total_used) }} / {{ $d->total_quota }} MB</div>
            <div style="width:100%;background:var(--border);height:4px;border-radius:2px;margin-top:.5rem">
                @php $pct = $d->total_quota > 0 ? min(100, ($d->total_used / $d->total_quota) * 100) : 0; @endphp
                <div style="width:{{ $pct }}%;background:{{ $pct > 90 ? 'var(--err)' : 'var(--accent)' }};height:100%;border-radius:2px"></div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="table-card">
        <div class="table-header"><div class="table-title">Largest Mailboxes</div></div>
        <table>
            <thead><tr><th>Mailbox</th><th>Domain</th><th>Messages</th><th>Used Space</th><th>Quota</th><th>Usage %</th></tr></thead>
            <tbody>
            @forelse($storage as $s)
                <tr>
                    <td style="font-weight:600">{{ $s->mailbox->email }}</td>
                    <td>{{ $s->domain->domain }}</td>
                    <td>{{ number_format($s->message_count) }}</td>
                    <td>{{ round($s->used_bytes / 1048576, 2) }} MB</td>
                    <td>{{ round($s->quota_bytes / 1048576, 2) }} MB</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:100px;background:var(--border);height:6px;border-radius:3px"><div style="width:{{ min(100, $s->percentage_used) }}%;background:{{ $s->percentage_used > 90 ? 'var(--err)' : 'var(--accent)' }};height:100%;border-radius:3px"></div></div>
                            <span style="font-size:.75rem;color:{{ $s->percentage_used > 90 ? 'var(--err)' : 'var(--t2)' }}">{{ round($s->percentage_used) }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No storage data calculated yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $storage->links() }}</div>
@endsection
