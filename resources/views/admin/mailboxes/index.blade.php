@extends('admin.layout')
@section('title', 'Mailboxes')
@section('actions')<a href="{{ route('admin.mailboxes.create') }}" class="btn btn-primary btn-sm">+ Add Mailbox</a>@endsection

@section('content')
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search email..." value="{{ request('search') }}">
                <select name="domain_id" class="form-input" style="width:200px">
                    <option value="">All Domains</option>
                    @foreach($domains as $d)
                        <option value="{{ $d->id }}" {{ request('domain_id') == $d->id ? 'selected' : '' }}>{{ $d->domain }}</option>
                    @endforeach
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Email</th><th>Name</th><th>Domain</th><th>Quota</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($mailboxes as $mb)
                <tr>
                    <td style="font-weight:600">{{ $mb->email }}</td>
                    <td>{{ $mb->name ?? '—' }}</td>
                    <td>{{ $mb->domain->domain }}</td>
                    <td>
                        <div style="font-size:.7rem;margin-bottom:.2rem">{{ $mb->getQuotaPercentage() }}% of {{ $mb->quota }}MB</div>
                        <div style="width:100%;background:var(--border);height:4px;border-radius:2px"><div style="width:{{ min(100, $mb->getQuotaPercentage()) }}%;background:{{ $mb->getQuotaPercentage() > 90 ? 'var(--err)' : 'var(--accent)' }};height:100%;border-radius:2px"></div></div>
                    </td>
                    <td><span class="badge {{ $mb->status === 'active' ? 'badge-ok' : 'badge-warn' }}">{{ $mb->status }}</span></td>
                    <td><a href="{{ route('admin.mailboxes.edit', $mb) }}" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No mailboxes yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $mailboxes->links() }}</div>
@endsection
