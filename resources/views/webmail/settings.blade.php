@extends('webmail.layout')
@section('title', 'Settings')

@section('styles')
<style>
    .settings-container{padding:2rem;max-width:800px;margin:0 auto;overflow-y:auto;height:100%}
    .settings-card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;box-shadow:var(--shadow)}
    .settings-card-title{font-size:1.1rem;font-weight:600;margin-bottom:1rem;border-bottom:1px solid var(--border);padding-bottom:.5rem}
    .settings-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
    .settings-grid-3{display:grid;grid-template-columns:1fr 1fr 2fr;gap:1rem;margin-bottom:1rem;align-items:end}
    .settings-grid-2-action{display:grid;grid-template-columns:1fr 2fr;gap:1rem;margin-bottom:1rem;align-items:end}
    .filter-table{width:100%;border-collapse:collapse;font-size:0.9rem}
    .filter-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:1.5rem}

    /* ===== RESPONSIVE: Mobile ===== */
    @media(max-width:768px){
        .settings-container{padding:1rem}
        .settings-card{padding:1rem}
        .settings-grid-2{grid-template-columns:1fr;gap:1rem}
        .settings-grid-3{grid-template-columns:1fr;gap:.75rem}
        .settings-grid-2-action{grid-template-columns:1fr;gap:.75rem}
    }

    /* ===== RESPONSIVE: Small Phone ===== */
    @media(max-width:480px){
        .settings-container{padding:.75rem}
        .settings-container h2{font-size:1.15rem}
        .settings-card{padding:.75rem;border-radius:8px}
        .settings-card-title{font-size:1rem}
    }
</style>
@endsection

@section('content')
<div class="settings-container">
    <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1.5rem">Mailbox Settings</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="settings-card">
        <h3 class="settings-card-title">Client Connection Details</h3>
        <p style="font-size:.85rem;color:var(--t2);margin-bottom:1rem">Use these settings to configure your email in third-party clients like Outlook, Apple Mail, or Thunderbird.</p>
        
        <div class="settings-grid-2">
            <div>
                <h4 style="font-size:.95rem;font-weight:600;margin-bottom:.5rem;color:var(--accent)">Incoming Server (IMAP)</h4>
                <div style="font-size:.85rem;line-height:1.6;color:var(--t1)">
                    <strong>Server:</strong> {{ config('edgemail.hostname') }}<br>
                    <strong>Port:</strong> 993<br>
                    <strong>Encryption:</strong> SSL/TLS<br>
                    <strong>Username:</strong> {{ $mailbox->email }}<br>
                    <strong>Password:</strong> <em>Your Mailbox Password</em>
                </div>
            </div>
            <div>
                <h4 style="font-size:.95rem;font-weight:600;margin-bottom:.5rem;color:var(--accent)">Outgoing Server (SMTP)</h4>
                <div style="font-size:.85rem;line-height:1.6;color:var(--t1)">
                    <strong>Server:</strong> {{ config('edgemail.hostname') }}<br>
                    <strong>Port:</strong> 465 (or 587)<br>
                    <strong>Encryption:</strong> SSL/TLS<br>
                    <strong>Username:</strong> {{ $mailbox->email }}<br>
                    <strong>Password:</strong> <em>Your Mailbox Password</em>
                </div>
            </div>
        </div>
    </div>
    
    <div class="settings-card">
        <h3 class="settings-card-title">Profile & Signature</h3>
        <form method="POST" action="{{ route('webmail.settings.update') }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Display Name (Sender Name)</label>
                <input type="text" name="name" class="form-control" value="{{ $mailbox->name }}" placeholder="John Doe">
                <p style="font-size:.85rem;color:var(--t2);margin-top:.5rem">This name will appear to recipients when you send an email.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Email Signature</label>
                <p style="font-size:.85rem;color:var(--t2);margin-bottom:.5rem">This signature will be appended to all outgoing emails.</p>
                <textarea name="signature" class="form-control" rows="5" placeholder="Best regards,&#10;{{ $mailbox->name ?? 'John Doe' }}">{{ $mailbox->signature }}</textarea>
            </div>
            <button class="btn btn-primary">Save Profile</button>
        </form>
    </div>
    
    <div class="settings-card">
        <h3 class="settings-card-title">Vacation / Autoresponder</h3>
        <form method="POST" action="{{ route('webmail.settings.update') }}">
            @csrf
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                <input type="checkbox" name="autoresponder_active" value="1" id="ar_active" {{ $autoresponder && $autoresponder->status === 'active' ? 'checked' : '' }} style="accent-color:var(--accent)">
                <label for="ar_active" style="font-weight:500">Enable Autoresponder</label>
            </div>
            
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="autoresponder_subject" class="form-control" value="{{ $autoresponder->subject ?? 'Out of office' }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">Message Body</label>
                <textarea name="autoresponder_body" class="form-control" rows="4">{{ $autoresponder->body ?? "I am currently away and will reply as soon as possible." }}</textarea>
            </div>
            
            <div class="settings-grid-2" style="margin-bottom:1rem">
                <div class="form-group">
                    <label class="form-label">Start Date (Optional)</label>
                    <input type="date" name="autoresponder_start" class="form-control" value="{{ $autoresponder->start_date ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date (Optional)</label>
                    <input type="date" name="autoresponder_end" class="form-control" value="{{ $autoresponder->end_date ?? '' }}">
                </div>
            </div>
            <button class="btn btn-primary">Save Autoresponder</button>
        </form>
    </div>
    
    <div class="settings-card">
        <h3 class="settings-card-title">Email Forwarding</h3>
        <form method="POST" action="{{ route('webmail.settings.update') }}">
            @csrf
            @php $rule = $forwarding->first(); @endphp
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                <input type="checkbox" name="forwarding_active" value="1" id="fwd_active" {{ $rule && $rule->status === 'active' ? 'checked' : '' }} style="accent-color:var(--accent)">
                <label for="fwd_active" style="font-weight:500">Enable Forwarding</label>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forward To Address</label>
                <input type="email" name="forwarding_destination" class="form-control" placeholder="external@gmail.com" value="{{ $rule->destination ?? '' }}">
            </div>
            
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                <input type="checkbox" name="forwarding_keep_copy" value="1" id="fwd_keep" {{ $rule && $rule->keep_copy ? 'checked' : '' }} style="accent-color:var(--accent)">
                <label for="fwd_keep" style="font-size:.85rem;color:var(--t2)">Keep a copy in this inbox (if unchecked, messages will only exist at destination)</label>
            </div>
            
            <button class="btn btn-primary">Save Forwarding</button>
        </form>
    </div>

    <div class="settings-card" style="margin-top:1.5rem">
        <h3 class="settings-card-title">Email Filters</h3>
        
        @if($filters && $filters->count() > 0)
            <div class="filter-table-wrap">
                <table class="filter-table">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border); text-align:left;">
                            <th style="padding:0.5rem">Name</th>
                            <th style="padding:0.5rem">Criteria</th>
                            <th style="padding:0.5rem">Action</th>
                            <th style="padding:0.5rem; width:50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filters as $filter)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:0.5rem">{{ $filter->name }}</td>
                                <td style="padding:0.5rem">If {{ $filter->criteria_field }} {{ $filter->criteria_operator }} "{{ $filter->criteria_value }}"</td>
                                <td style="padding:0.5rem">{{ ucfirst(str_replace('_', ' ', $filter->action)) }} {{ $filter->action_value ? '-> ' . $filter->action_value : '' }}</td>
                                <td style="padding:0.5rem;">
                                    <form method="POST" action="{{ route('webmail.settings.filter.destroy', $filter->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" style="padding:4px 8px; font-size:0.8rem">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="POST" action="{{ route('webmail.settings.filter.add') }}">
            @csrf
            <div class="settings-grid-2" style="margin-bottom:1rem">
                <div class="form-group">
                    <label class="form-label">Filter Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Marketing emails" required>
                </div>
            </div>
            
            <div class="settings-grid-3">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">If</label>
                    <select name="criteria_field" class="form-control" required>
                        <option value="sender">Sender</option>
                        <option value="subject">Subject</option>
                        <option value="body">Body</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <select name="criteria_operator" class="form-control" required>
                        <option value="contains">Contains</option>
                        <option value="equals">Equals</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <input type="text" name="criteria_value" class="form-control" placeholder="Value to match" required>
                </div>
            </div>

            <div class="settings-grid-2-action">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Then</label>
                    <select name="action" class="form-control" required id="filter_action">
                        <option value="move_to">Move to Folder</option>
                        <option value="delete">Delete Message</option>
                        <option value="mark_read">Mark as Read</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0" id="filter_action_value_container">
                    <input type="text" name="action_value" class="form-control" placeholder="Folder Name (e.g. Junk, Archive)" id="filter_action_value">
                </div>
            </div>

            <button class="btn btn-primary">Add Filter</button>
        </form>
        <script>
            document.getElementById('filter_action').addEventListener('change', function() {
                const val = this.value;
                const container = document.getElementById('filter_action_value_container');
                if(val === 'move_to') {
                    container.style.display = 'block';
                    document.getElementById('filter_action_value').required = true;
                } else {
                    container.style.display = 'none';
                    document.getElementById('filter_action_value').required = false;
                }
            });
        </script>
    </div>
</div>
@endsection
