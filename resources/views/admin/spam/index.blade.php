@extends('admin.layout')
@section('title', 'Spam Filters')
@section('content')
    <div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem">
        <div class="table-card" style="padding:1.5rem;align-self:start">
            <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">Add Global Filter</h3>
            <form method="POST" action="{{ route('admin.spam.store-filter') }}">
                @csrf
                <div class="form-group"><label class="form-label">Domain (Optional)</label><select name="domain_id" class="form-input"><option value="">All Domains (Global)</option>@foreach($domains as $d)<option value="{{ $d->id }}">{{ $d->domain }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Filter Type</label><select name="filter_type" class="form-input"><option value="blacklist">Blacklist</option><option value="whitelist">Whitelist</option></select></div>
                <div class="form-group"><label class="form-label">Value (Email or Domain)</label><input type="text" name="value" class="form-input" placeholder="*@spammer.com" required></div>
                <div class="form-group"><label class="form-label">Action</label><select name="action" class="form-input"><option value="reject">Reject</option><option value="mark">Mark as Spam</option><option value="accept">Accept (Whitelist)</option></select></div>
                <button class="btn btn-primary" style="width:100%">Add Filter</button>
            </form>
        </div>
        <div class="table-card">
            <table>
                <thead><tr><th>Scope</th><th>Type</th><th>Value</th><th>Action</th><th>Status</th><th>Delete</th></tr></thead>
                <tbody>
                @forelse($filters as $f)
                    <tr>
                        <td>{{ $f->domain_id ? $f->domain->domain : 'Global' }}</td>
                        <td><span class="badge {{ $f->filter_type === 'whitelist' ? 'badge-ok' : 'badge-err' }}">{{ $f->filter_type }}</span></td>
                        <td style="font-family:monospace">{{ $f->value }}</td>
                        <td>{{ $f->action }}</td>
                        <td><span class="badge {{ $f->status === 'active' ? 'badge-ok' : 'badge-warn' }}">{{ $f->status }}</span></td>
                        <td><form method="POST" action="{{ route('admin.spam.destroy-filter', $f) }}" onsubmit="return confirm('Remove filter?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">🗑</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No custom spam filters set.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="pagination">{{ $filters->links() }}</div>
        </div>
    </div>
@endsection
