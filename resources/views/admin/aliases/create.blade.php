@extends('admin.layout')
@section('title', 'Add Alias')
@section('content')
    <div style="max-width:600px">
        <div class="table-card" style="padding:1.5rem">
            <form method="POST" action="{{ route('admin.aliases.store') }}">
                @csrf
                <div class="form-group"><label class="form-label">Domain</label><select name="domain_id" class="form-input" required>@foreach($domains as $d)<option value="{{ $d->id }}">{{ $d->domain }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Alias Address (Source)</label><input type="text" class="form-input" name="source" placeholder="alias@domain.com" required></div>
                <div class="form-group"><label class="form-label">Destination Addresses (Comma separated)</label><textarea class="form-input" name="destination" required rows="3" placeholder="user@domain.com, external@gmail.com"></textarea></div>
                <div class="form-group"><label class="form-label">Comment / Notes</label><input type="text" class="form-input" name="comment"></div>
                <div style="display:flex;gap:.5rem;margin-top:1rem"><button type="submit" class="btn btn-primary">Create Alias</button><a href="{{ route('admin.aliases.index') }}" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
@endsection
