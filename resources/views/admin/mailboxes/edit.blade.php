@extends('admin.layout')
@section('title', 'Edit ' . $mailbox->email)
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.mailboxes.update', $mailbox) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Email</label><input type="text" class="form-input" value="{{ $mailbox->email }}" disabled></div>
                <div class="form-group"><label class="form-label">Display Name</label><input type="text" class="form-input" name="name" value="{{ $mailbox->name }}"></div>
                <div class="form-group"><label class="form-label">New Password (leave blank to keep current)</label><input type="password" class="form-input" name="password" minlength="8"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Quota (MB)</label><input type="number" class="form-input" name="quota" value="{{ $mailbox->quota }}"></div>
                    <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" {{ $mailbox->status==='active'?'selected':'' }}>Active</option><option value="inactive" {{ $mailbox->status==='inactive'?'selected':'' }}>Inactive</option></select></div>
                </div>
                <div style="margin-top:1rem">
                    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;font-size:.85rem"><input type="checkbox" name="is_catchall" value="1" {{ $mailbox->is_catchall ? 'checked' : '' }} style="accent-color:var(--accent)"> Set as Domain Catch-all</label>
                    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;font-size:.85rem"><input type="checkbox" name="send_only" value="1" {{ $mailbox->send_only ? 'checked' : '' }} style="accent-color:var(--accent)"> Send Only (Cannot receive)</label>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1.5rem"><button type="submit" class="btn btn-primary">Update Mailbox</button><a href="{{ route('admin.mailboxes.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <form method="POST" action="{{ route('admin.mailboxes.destroy', $mailbox) }}" style="margin-top:1rem" onsubmit="return confirm('Delete this mailbox? All emails will be lost.')">@csrf @method('DELETE')<button class="btn btn-danger">Delete Mailbox</button></form>
    </div>
@endsection
