@extends('admin.layout')
@section('title', 'Edit User')
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input" name="name" value="{{ $user->name }}" required></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" name="email" value="{{ $user->email }}" required></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="user" {{ $user->role=='user'?'selected':'' }}>User</option>
                        <option value="reseller" {{ $user->role=='reseller'?'selected':'' }}>Reseller</option>
                        @if(auth()->user()->isSuperAdmin())
                        <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
                        <option value="super_admin" {{ $user->role=='super_admin'?'selected':'' }}>Super Admin</option>
                        @endif
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" {{ $user->status==='active'?'selected':'' }}>Active</option><option value="inactive" {{ $user->status==='inactive'?'selected':'' }}>Inactive</option></select></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Domains</label><input type="number" class="form-input" name="max_domains" value="{{ $user->max_domains }}" min="1"></div>
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="{{ $user->max_mailboxes }}" min="1"></div>
                </div>
                <div class="form-group"><label class="form-label">Change Password (leave blank to keep current)</label><input type="password" class="form-input" name="password" minlength="8"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Update User</button><a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        @if(!$user->isSuperAdmin() || \App\Models\User::role('super_admin')->count() > 1)
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="margin-top:1rem" onsubmit="return confirm('Delete this user? This will also delete their domains and mailboxes.')">@csrf @method('DELETE')<button class="btn btn-danger">Delete User</button></form>
        @endif
    </div>
@endsection
