@extends('admin.layout')
@section('title', 'Add Domain')
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.domains.store') }}">
                @csrf
                <div class="form-group"><label class="form-label">Domain Name</label><input type="text" class="form-input" name="domain" placeholder="example.com" required value="{{ old('domain') }}"></div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" class="form-input" name="description" placeholder="Optional description" value="{{ old('description') }}"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Max Mailboxes</label><input type="number" class="form-input" name="max_mailboxes" value="10" min="1"></div>
                    <div class="form-group"><label class="form-label">Max Aliases</label><input type="number" class="form-input" name="max_aliases" value="50" min="1"></div>
                </div>
                <div class="form-group"><label class="form-label">Max Quota (MB)</label><input type="number" class="form-input" name="max_quota" value="10240" min="100"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create Domain</button><a href="{{ route('admin.domains.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
@endsection
