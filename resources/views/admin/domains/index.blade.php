@extends('admin.layout')
@section('title', 'Domains')
@section('actions')<a href="{{ route('admin.domains.create') }}" class="btn btn-primary btn-sm">+ Add Domain</a>@endsection

@section('content')
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem"><input type="text" name="search" class="form-input" style="width:250px" placeholder="Search domains..." value="{{ request('search') }}"><button class="btn btn-secondary btn-sm">Search</button></form>
        </div>
        <table>
            <thead><tr><th>Domain</th><th>Owner</th><th>Mailboxes</th><th>Aliases</th><th>Status</th><th>DKIM</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($domains as $domain)
                <tr>
                    <td style="font-weight:600">{{ $domain->domain }}</td>
                    <td>{{ $domain->user->name ?? '—' }}</td>
                    <td>{{ $domain->mailboxes_count }}/{{ $domain->max_mailboxes }}</td>
                    <td>{{ $domain->aliases_count }}/{{ $domain->max_aliases }}</td>
                    <td><span class="badge {{ $domain->status === 'active' ? 'badge-ok' : 'badge-warn' }}">{{ $domain->status }}</span></td>
                    <td><span class="badge {{ $domain->dkim_enabled ? 'badge-ok' : 'badge-warn' }}">{{ $domain->dkim_enabled ? 'On' : 'Off' }}</span></td>
                    <td><a href="{{ route('admin.domains.show', $domain) }}" class="btn btn-secondary btn-sm">View</a> <a href="{{ route('admin.domains.edit', $domain) }}" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:var(--t3);padding:2rem">No domains yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $domains->links() }}</div>
@endsection
