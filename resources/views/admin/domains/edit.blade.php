@extends('admin.layout')
@section('title', 'Edit ' . $domain->domain)
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.domains.update', $domain) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Domain</label><input type="text" class="form-input" name="domain" value="{{ $domain->domain }}" required></div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" class="form-input" name="description" value="{{ $domain->description }}"></div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" {{ $domain->status==='active'?'selected':'' }}>Active</option><option value="inactive" {{ $domain->status==='inactive'?'selected':'' }}>Inactive</option><option value="pending" {{ $domain->status==='pending'?'selected':'' }}>Pending</option></select></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="{{ $domain->max_mailboxes }}"></div>
                    <div class="form-group"><label class="form-label">Max Aliases</label><input type="number" class="form-input" name="max_aliases" value="{{ $domain->max_aliases }}"></div>
                </div>
                <div class="form-group"><label class="form-label">Catch-All Email</label><input type="email" class="form-input" name="catch_all" value="{{ $domain->catch_all }}" placeholder="catchall@{{ $domain->domain }}"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button class="btn btn-primary">Update</button><a href="{{ route('admin.domains.show', $domain) }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <form method="POST" action="{{ route('admin.domains.destroy', $domain) }}" style="margin-top:1rem" onsubmit="return confirm('Delete this domain and all its mailboxes?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete Domain</button></form>
    </div>
@endsection
