@extends('admin.layout')
@section('title', 'Add User')
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input" name="name" required value="{{ old('name') }}"></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" name="email" required value="{{ old('email') }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-input" name="password" required minlength="8"></div>
                    <div class="form-group"><label class="form-label">Confirm Password</label><input type="password" class="form-input" name="password_confirmation" required minlength="8"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="user">User</option>
                        <option value="reseller">Reseller</option>
                        @if(auth()->user()->isSuperAdmin())
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        @endif
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Domains</label><input type="number" class="form-input" name="max_domains" value="1" min="1"></div>
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="10" min="1"></div>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create User</button><a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
@endsection
