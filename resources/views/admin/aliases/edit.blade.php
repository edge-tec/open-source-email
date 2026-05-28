@extends('admin.layout')
@section('title', 'Edit Alias')
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.aliases.update', $alias) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Alias Address</label><input type="text" class="form-input" name="source" value="{{ $alias->source }}" required></div>
                <div class="form-group"><label class="form-label">Destination</label><textarea class="form-input" name="destination" required rows="3">{{ $alias->destination }}</textarea></div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-input"><option value="active" {{ $alias->status==='active'?'selected':'' }}>Active</option><option value="inactive" {{ $alias->status==='inactive'?'selected':'' }}>Inactive</option></select></div>
                <div class="form-group"><label class="form-label">Comment</label><input type="text" class="form-input" name="comment" value="{{ $alias->comment }}"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Update Alias</button><a href="{{ route('admin.aliases.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <form method="POST" action="{{ route('admin.aliases.destroy', $alias) }}" style="margin-top:1rem" onsubmit="return confirm('Delete this alias?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete Alias</button></form>
    </div>
@endsection
