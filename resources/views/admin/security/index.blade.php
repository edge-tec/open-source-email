@extends('admin.layout')
@section('title', 'Security')
@section('content')
    <div class="stat-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-label">Active IP Bans</div><div class="stat-value" style="color:var(--err)">{{ $stats['active_bans'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Failed Logins (Today)</div><div class="stat-value" style="color:var(--warn)">{{ $stats['failed_today'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Failed Logins (Week)</div><div class="stat-value">{{ $stats['failed_week'] }}</div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1.5rem">
        <div class="table-card">
            <div class="table-header"><div class="table-title">Currently Banned IPs</div></div>
            <table>
                <thead><tr><th>IP Address</th><th>Banned Until</th></tr></thead>
                <tbody>
                @forelse($bannedIps as $ban)
                    <tr>
                        <td style="font-family:monospace;color:var(--err)">{{ $ban->ip_address }}</td>
                        <td>{{ $ban->banned_until->diffForHumans() }} ({{ $ban->banned_until->format('H:i') }})</td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="text-align:center;color:var(--t3);padding:2rem">No active bans.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <div class="table-header"><div class="table-title">Recent Failed Logins</div></div>
            <table>
                <thead><tr><th>IP</th><th>Email Attempted</th><th>Time</th></tr></thead>
                <tbody>
                @forelse($recentFails as $fail)
                    <tr>
                        <td style="font-family:monospace;font-size:.8rem">{{ $fail->ip_address }}</td>
                        <td>{{ $fail->email }}</td>
                        <td style="font-size:.75rem">{{ $fail->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--t3);padding:2rem">No recent failures.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="table-card" style="grid-column: 1 / -1;">
            <div class="table-header"><div class="table-title">SSL Configuration</div></div>
            <div style="padding: 1.5rem">
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;">1. Auto-Generate Self-Signed Certificate</h4>
                    <p style="font-size: 0.85rem; color: var(--t2); margin-bottom: 1rem;">Click below to instantly generate a self-signed SSL certificate for development or internal use.</p>
                    <form method="POST" action="{{ route('admin.security.ssl.self-signed') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will restart the mail server. Continue?')">Generate Self-Signed SSL</button>
                    </form>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 2rem 0;">

                <div>
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;">2. Install Custom / Let's Encrypt SSL</h4>
                    <p style="font-size: 0.85rem; color: var(--t2); margin-bottom: 1rem;">Paste your fullchain certificate and private key below to install a custom SSL certificate.</p>
                    <form method="POST" action="{{ route('admin.security.ssl.custom') }}">
                        @csrf
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Certificate (CRT/Fullchain)</label>
                            <textarea name="certificate" class="form-input" rows="5" placeholder="-----BEGIN CERTIFICATE-----" required></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Private Key (KEY)</label>
                            <textarea name="private_key" class="form-input" rows="5" placeholder="-----BEGIN PRIVATE KEY-----" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will restart the mail server. Continue?')">Install SSL</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
