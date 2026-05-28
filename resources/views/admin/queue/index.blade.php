@extends('admin.layout')
@section('title', 'Mail Queue')
@section('content')
    <div class="stat-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Queued</div><div class="stat-value" style="color:var(--info)">{{ $stats['queued'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value" style="color:var(--ok)">{{ $stats['active'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Deferred</div><div class="stat-value" style="color:var(--warn)">{{ $stats['deferred'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Bounced</div><div class="stat-value" style="color:var(--err)">{{ $stats['bounced'] }}</div></div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>ID</th><th>Sender</th><th>Recipient</th><th>Status</th><th>Attempts</th><th>Next Retry</th><th>Error</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($queue as $q)
                <tr>
                    <td style="font-family:monospace;font-size:.75rem">{{ substr($q->queue_id, 0, 8) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($q->sender, 20) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($q->recipient, 20) }}</td>
                    <td><span class="badge {{ $q->status === 'queued' ? 'badge-info' : ($q->status === 'deferred' ? 'badge-warn' : 'badge-err') }}">{{ $q->status }}</span></td>
                    <td>{{ $q->attempts }}</td>
                    <td style="font-size:.75rem">{{ $q->next_retry_at ? $q->next_retry_at->diffForHumans() : '—' }}</td>
                    <td style="color:var(--err);font-size:.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $q->error_message }}">{{ $q->error_message ?? '—' }}</td>
                    <td style="display:flex;gap:.25rem">
                        <form method="POST" action="{{ route('admin.queue.retry', $q->id) }}">@csrf<button class="btn btn-secondary btn-sm" title="Retry">↻</button></form>
                        <form method="POST" action="{{ route('admin.queue.destroy', $q->id) }}" onsubmit="return confirm('Remove message from queue? It will not be sent.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Delete">🗑</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:var(--t3);padding:2rem">Queue is empty.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $queue->links() }}</div>
@endsection
