@extends('admin.layout')
@section('title', 'Email Logs')
@section('content')
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search emails..." value="{{ request('search') }}">
                <select name="direction" class="form-input" style="width:130px">
                    <option value="">All Types</option>
                    <option value="inbound" {{ request('direction')=='inbound'?'selected':'' }}>Inbound</option>
                    <option value="outbound" {{ request('direction')=='outbound'?'selected':'' }}>Outbound</option>
                </select>
                <select name="status" class="form-input" style="width:130px">
                    <option value="">All Statuses</option>
                    <option value="delivered" {{ request('status')=='delivered'?'selected':'' }}>Delivered</option>
                    <option value="sent" {{ request('status')=='sent'?'selected':'' }}>Sent</option>
                    <option value="bounced" {{ request('status')=='bounced'?'selected':'' }}>Bounced</option>
                    <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                    <option value="deferred" {{ request('status')=='deferred'?'selected':'' }}>Deferred</option>
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Time</th><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Status</th><th>Spam Score</th><th>Details</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="font-size:.75rem">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td><span class="badge {{ $log->direction === 'inbound' ? 'badge-info' : 'badge-ok' }}">{{ substr($log->direction, 0, 3) }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
                            <span title="{{ $log->sender }}">{{ \Illuminate\Support\Str::limit($log->sender, 20) }}</span>
                            <a href="{{ route('admin.logs.index', ['search' => $log->sender]) }}" style="color:var(--t3);text-decoration:none;font-size:.8rem" title="Filter by this sender">🔍</a>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
                            <span title="{{ $log->recipient }}">{{ \Illuminate\Support\Str::limit($log->recipient, 20) }}</span>
                            <a href="{{ route('admin.logs.index', ['search' => $log->recipient]) }}" style="color:var(--t3);text-decoration:none;font-size:.8rem" title="Filter by this recipient">🔍</a>
                        </div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($log->subject, 30) }}</td>
                    <td><span class="badge {{ in_array($log->status, ['sent', 'delivered']) ? 'badge-ok' : (in_array($log->status, ['bounced', 'rejected']) ? 'badge-err' : 'badge-warn') }}">{{ $log->status }}</span></td>
                    <td>{{ $log->direction === 'inbound' ? $log->spam_score : '—' }}</td>
                    <td><a href="{{ route('admin.logs.show', $log) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:var(--t3);padding:2rem">No logs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $logs->links() }}</div>
@endsection
