@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Domains</div>
            <div class="stat-value">{{ number_format($stats['total_domains']) }}</div>
            <div class="stat-change" style="color:var(--ok)">{{ $stats['active_domains'] }} active</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Mailboxes</div>
            <div class="stat-value">{{ number_format($stats['total_mailboxes']) }}</div>
            <div class="stat-change" style="color:var(--ok)">{{ $stats['active_mailboxes'] }} active</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Emails Today</div>
            <div class="stat-value">{{ number_format($stats['emails_today']) }}</div>
            <div class="stat-change">↑ {{ $stats['emails_sent'] }} sent · ↓ {{ $stats['emails_received'] }} received</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Spam Blocked</div>
            <div class="stat-value" style="color:var(--err)">{{ number_format($stats['spam_blocked']) }}</div>
            <div class="stat-change" style="color:var(--t3)">Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Queue Size</div>
            <div class="stat-value" style="color:var(--warn)">{{ number_format($stats['queue_size']) }}</div>
            <div class="stat-change">Pending delivery</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-change" style="color:var(--ok)">{{ $stats['active_users'] }} active</div>
        </div>
    </div>

    <div class="table-card" style="margin-bottom:1.5rem">
        <div class="table-header"><div class="table-title">System Resources</div></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1.5rem;padding:1.5rem">
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">CPU Load (1m)</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--t1)">{{ $systemStats['cpu_load'] }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Total</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--t1)">{{ $systemStats['ram_total'] ? $systemStats['ram_total'] . ' MB' : 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Used</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--warn)">{{ $systemStats['ram_used'] ? $systemStats['ram_used'] . ' MB' : 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Free</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--ok)">{{ $systemStats['ram_free'] ? $systemStats['ram_free'] . ' MB' : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="table-card" style="margin-bottom:1.5rem">
        <div class="table-header"><div class="table-title">Email Activity (Last 7 Days)</div></div>
        <div style="padding:1.5rem;height:300px">
            <canvas id="emailChart"></canvas>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('emailChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'Sent',
                        data: {!! json_encode($chartData['sent']) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Received',
                        data: {!! json_encode($chartData['received']) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Active Users',
                        data: {!! json_encode($chartData['active_users']) !!},
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#9ca3af', font: { family: 'system-ui' } } }
                },
                scales: {
                    x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { beginAtZero: true, ticks: { color: '#6b7280', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    </script>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(400px, 1fr));gap:1rem">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">Recent Email Activity</div>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <table>
                <thead><tr><th>Direction</th><th>From</th><th>To</th><th>Subject</th><th>Status</th><th>Time</th></tr></thead>
                <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td><span class="badge {{ $log->direction === 'inbound' ? 'badge-info' : 'badge-ok' }}">{{ $log->direction }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($log->sender, 25) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($log->recipient, 25) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($log->subject, 30) }}</td>
                        <td><span class="badge {{ $log->status === 'sent' || $log->status === 'delivered' ? 'badge-ok' : ($log->status === 'bounced' || $log->status === 'rejected' ? 'badge-err' : 'badge-warn') }}">{{ $log->status }}</span></td>
                        <td style="font-size:.75rem">{{ $log->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No email activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <div class="table-header"><div class="table-title">Recent Users</div></div>
            <table>
                <thead><tr><th>Name</th><th>Role</th></tr></thead>
                <tbody>
                @foreach($recentUsers as $user)
                    <tr>
                        <td>
                            <div style="font-weight:500">{{ $user->name }}</div>
                            <div style="font-size:.7rem;color:var(--t3)">{{ $user->email }}</div>
                        </td>
                        <td><span class="badge badge-info">{{ $user->role }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
