@extends('admin.layout')
@section('title', 'Users')
@section('actions')<a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Add User</a>@endsection
@section('content')
    <div class="table-card">
        <div class="table-header">
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-input" style="width:250px" placeholder="Search users..." value="{{ request('search') }}">
                <select name="role" class="form-input" style="width:150px">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ request('role')=='super_admin'?'selected':'' }}>Super Admin</option>
                    <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
                    <option value="reseller" {{ request('role')=='reseller'?'selected':'' }}>Reseller</option>
                    <option value="user" {{ request('role')=='user'?'selected':'' }}>User</option>
                </select>
                <button class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>
        <table>
            <thead><tr><th>Name</th><th>Role</th><th>Domains</th><th>Mailboxes</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><div style="font-weight:600">{{ $user->name }}</div><div style="font-size:.75rem;color:var(--t3)">{{ $user->email }}</div></td>
                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                    <td>{{ $user->domains_count }}/{{ $user->max_domains }}</td>
                    <td>{{ $user->mailboxes_count }}/{{ $user->max_mailboxes }}</td>
                    <td><span class="badge {{ $user->status === 'active' ? 'badge-ok' : 'badge-warn' }}">{{ $user->status }}</span></td>
                    <td><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $users->links() }}</div>
@endsection
