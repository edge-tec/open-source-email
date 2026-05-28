@extends('admin.layout')
@section('title', 'Aliases')
@section('actions')<a href="{{ route('admin.aliases.create') }}" class="btn btn-primary btn-sm">+ Add Alias</a>@endsection
@section('content')
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem"><input type="text" name="search" class="form-input" style="width:250px" placeholder="Search aliases..." value="{{ request('search') }}"><button class="btn btn-secondary btn-sm">Search</button></form>
        </div>
        <table>
            <thead><tr><th>Source</th><th>Destination</th><th>Domain</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($aliases as $alias)
                <tr>
                    <td style="font-weight:600">{{ $alias->source }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($alias->destination, 40) }}</td>
                    <td>{{ $alias->domain->domain }}</td>
                    <td><span class="badge {{ $alias->status === 'active' ? 'badge-ok' : 'badge-warn' }}">{{ $alias->status }}</span></td>
                    <td><a href="{{ route('admin.aliases.edit', $alias) }}" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:var(--t3);padding:2rem">No aliases yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $aliases->links() }}</div>
@endsection
