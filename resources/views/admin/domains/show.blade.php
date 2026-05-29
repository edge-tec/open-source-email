@extends('admin.layout')
@section('title', $domain->domain)
@section('content')
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Mailboxes</div><div class="stat-value">{{ $domain->mailboxes->count() }}/{{ $domain->max_mailboxes }}</div></div>
        <div class="stat-card"><div class="stat-label">Aliases</div><div class="stat-value">{{ $domain->aliases->count() }}/{{ $domain->max_aliases }}</div></div>
    </div>

    <div class="table-card" style="margin-bottom:1.5rem">
        <div class="table-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div class="table-title">DNS Configuration</div>
            <div>
                <button type="button" class="btn btn-primary btn-sm" id="verifyDnsBtn">
                    <span id="verifyDnsText">Verify DNS Records</span>
                    <span id="verifyDnsSpinner" style="display:none;">⏳</span>
                </button>
            </div>
        </div>
        <div style="padding:1.5rem; overflow-x:auto;">
            <p style="font-size:.85rem;color:var(--t2);margin-bottom:1rem">
                Add these records to your domain's DNS manager (e.g. Cloudflare, GoDaddy). Changes may take a few minutes to propagate.
            </p>
            
            <table style="width:100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr>
                        <th style="width: 10%;">Type</th>
                        <th style="width: 25%;">Host / Name</th>
                        <th style="width: 50%;">Value / Data</th>
                        <th style="width: 15%; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- MX Record -->
                    <tr>
                        <td style="font-weight:600; color:var(--t1);">MX</td>
                        <td style="font-family:monospace; font-size:.8rem;">@ <em>(or {{ $domain->domain }})</em></td>
                        <td style="font-family:monospace; font-size:.8rem; word-break:break-all;">10 mail.{{ $domain->domain }}</td>
                        <td style="text-align:center;" id="status-mx"><span class="badge" style="background:var(--bg4);color:var(--t2)">Pending</span></td>
                    </tr>
                    
                    <!-- SPF Record -->
                    <tr>
                        <td style="font-weight:600; color:var(--t1);">TXT <span style="font-size:.7rem;color:var(--t3)">(SPF)</span></td>
                        <td style="font-family:monospace; font-size:.8rem;">@</td>
                        <td style="font-family:monospace; font-size:.8rem; word-break:break-all;">{{ $domain->spf_record ?? 'v=spf1 mx a ~all' }}</td>
                        <td style="text-align:center;" id="status-spf"><span class="badge" style="background:var(--bg4);color:var(--t2)">Pending</span></td>
                    </tr>
                    
                    <!-- DMARC Record -->
                    <tr>
                        <td style="font-weight:600; color:var(--t1);">TXT <span style="font-size:.7rem;color:var(--t3)">(DMARC)</span></td>
                        <td style="font-family:monospace; font-size:.8rem;">_dmarc</td>
                        <td style="font-family:monospace; font-size:.8rem; word-break:break-all;">{{ $domain->dmarc_record ?? 'Not configured' }}</td>
                        <td style="text-align:center;" id="status-dmarc"><span class="badge" style="background:var(--bg4);color:var(--t2)">Pending</span></td>
                    </tr>
                    
                    <!-- DKIM Record -->
                    <tr>
                        <td style="font-weight:600; color:var(--t1);">TXT <span style="font-size:.7rem;color:var(--t3)">(DKIM)</span></td>
                        @if($domain->dkim_enabled && $domain->dkimKeys->count())
                            <td style="font-family:monospace; font-size:.8rem;">{{ $domain->dkim_selector }}._domainkey</td>
                            <td style="font-family:monospace; font-size:.8rem; word-break:break-all;">{{ $domain->dkimKeys->first()->dns_record }}</td>
                            <td style="text-align:center;" id="status-dkim"><span class="badge" style="background:var(--bg4);color:var(--t2)">Pending</span></td>
                        @else
                            <td colspan="3">
                                <form method="POST" action="{{ route('admin.domains.generate-dkim', $domain) }}" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-secondary btn-sm">Generate DKIM Key</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem">
            <h3 style="font-size:.9rem;font-weight:600;margin:0">SSL Configuration (SNI)</h3>
            <div style="display:flex; align-items:center; gap:1rem;">
                <span id="sslStatusBadge" class="badge" style="background:var(--bg4);color:var(--t2)">Status: Unknown</span>
                <button type="button" class="btn btn-secondary btn-sm" id="verifySslBtn">
                    <span id="verifySslText">Verify SSL</span>
                    <span id="verifySslSpinner" style="display:none;">⏳</span>
                </button>
            </div>
        </div>
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

    <script>
        document.getElementById('verifyDnsBtn')?.addEventListener('click', function() {
            const btn = this;
            const text = document.getElementById('verifyDnsText');
            const spinner = document.getElementById('verifyDnsSpinner');
            
            // Set loading state
            btn.disabled = true;
            text.innerText = "Verifying...";
            spinner.style.display = "inline-block";
            
            // Reset statuses to 'Checking...'
            const records = ['mx', 'spf', 'dmarc', 'dkim'];
            records.forEach(type => {
                const el = document.getElementById(`status-${type}`);
                if (el) el.innerHTML = `<span class="badge" style="background:var(--info);color:white">Checking</span>`;
            });

            // Perform fetch
            fetch("{{ route('admin.domains.verify-dns', $domain) }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.status) {
                        records.forEach(type => {
                            const el = document.getElementById(`status-${type}`);
                            if (el && data.status[type] !== undefined) {
                                if (data.status[type] === true) {
                                    el.innerHTML = `<span class="badge badge-ok">✅ Verified</span>`;
                                } else {
                                    el.innerHTML = `<span class="badge badge-err">❌ Missing</span>`;
                                }
                            }
                        });
                    } else {
                        alert("Failed to verify DNS. Please try again.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("A network error occurred.");
                })
                .finally(() => {
                    // Reset button state
                    btn.disabled = false;
                    text.innerText = "Verify DNS Records";
                    spinner.style.display = "none";
                });
        });

        document.getElementById('verifySslBtn')?.addEventListener('click', function() {
            const btn = this;
            const text = document.getElementById('verifySslText');
            const spinner = document.getElementById('verifySslSpinner');
            const statusBadge = document.getElementById('sslStatusBadge');
            
            // Set loading state
            btn.disabled = true;
            text.innerText = "Verifying...";
            spinner.style.display = "inline-block";
            statusBadge.innerHTML = "Checking...";
            statusBadge.style.background = "var(--info)";
            statusBadge.style.color = "white";

            // Perform fetch
            fetch("{{ route('admin.domains.verify-ssl', $domain) }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.verified) {
                            statusBadge.innerHTML = "✅ SSL Verified Successfully";
                            statusBadge.style.background = "rgba(16,185,129,.15)";
                            statusBadge.style.color = "var(--ok)";
                        } else {
                            statusBadge.innerHTML = "❌ " + data.message;
                            statusBadge.style.background = "rgba(239,68,68,.15)";
                            statusBadge.style.color = "var(--err)";
                        }
                    } else {
                        alert("Failed to verify SSL. Please try again.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("A network error occurred.");
                })
                .finally(() => {
                    // Reset button state
                    btn.disabled = false;
                    text.innerText = "Verify SSL";
                    spinner.style.display = "none";
                });
        });
    </script>
@endsection
