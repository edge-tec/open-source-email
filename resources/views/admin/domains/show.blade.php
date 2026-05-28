@extends('admin.layout')
@section('title', $domain->domain)
@section('content')
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Mailboxes</div><div class="stat-value">{{ $domain->mailboxes->count() }}/{{ $domain->max_mailboxes }}</div></div>
        <div class="stat-card"><div class="stat-label">Aliases</div><div class="stat-value">{{ $domain->aliases->count() }}/{{ $domain->max_aliases }}</div></div>
    </div>

    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">DNS Records</h3>
        <div style="background:var(--bg);padding:1rem;border-radius:8px;font-family:monospace;font-size:.8rem;color:var(--t2);margin-bottom:.75rem;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">MX Record:</strong> {{ $domain->domain }}. IN MX 10 {{ config('edgemail.hostname') }}.</div>
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">SPF:</strong> {{ $domain->spf_record ?? 'v=spf1 mx a ~all' }}</div>
            <div style="margin-bottom:.5rem"><strong style="color:var(--t1)">DMARC:</strong> {{ $domain->dmarc_record ?? 'Not configured' }}</div>
            @if($domain->dkim_enabled && $domain->dkimKeys->count())
            <div style="word-break:break-all;"><strong style="color:var(--t1)">DKIM:</strong> {{ $domain->dkimKeys->first()->dns_record ?? 'Key generated' }}</div>
            @endif
        </div>
        @if(!$domain->dkim_enabled)
        <form method="POST" action="{{ route('admin.domains.generate-dkim', $domain) }}">@csrf<button class="btn btn-primary btn-sm">Generate DKIM Key</button></form>
        @endif
    </div>

    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">SSL Configuration (SNI)</h3>
        <p style="font-size:.85rem;color:var(--t2);margin-bottom:1rem">Upload a custom SSL certificate for <strong>mail.{{ $domain->domain }}</strong> to enable secure connections for this domain.</p>
        <form method="POST" action="{{ route('admin.domains.ssl', $domain) }}">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1rem;margin-bottom:1rem">
                <div class="form-group">
                    <label class="form-label">Certificate (CRT/Fullchain)</label>
                    <textarea name="certificate" class="form-input" rows="4" placeholder="-----BEGIN CERTIFICATE-----" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Private Key (KEY)</label>
                    <textarea name="private_key" class="form-input" rows="4" placeholder="-----BEGIN PRIVATE KEY-----" required></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('This will restart the mail server. Continue?')">Install SSL for {{ $domain->domain }}</button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header"><div class="table-title">Mailboxes</div></div>
        <table>
            <thead><tr><th>Email</th><th>Name</th><th>Quota</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($domain->mailboxes as $mb)
                <tr><td>{{ $mb->email }}</td><td>{{ $mb->name }}</td><td>{{ $mb->getQuotaPercentage() }}%</td><td><span class="badge badge-ok">{{ $mb->status }}</span></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
