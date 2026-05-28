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
        <div class="table-header">
            <div class="table-title">System Resources (Live)</div>
            <div style="font-size:0.75rem; color:var(--ok); display:flex; align-items:center; gap:0.4rem;">
                <span style="display:inline-block; width:8px; height:8px; background:var(--ok); border-radius:50%; animation: pulse 2s infinite;"></span> Live Updates
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1.5rem;padding:1.5rem">
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">CPU Load</div>
                <div id="live-cpu" style="font-size:1.5rem;font-weight:700;color:var(--t1)">{{ $systemStats['cpu_load'] }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Total</div>
                <div id="live-ram-total" style="font-size:1.5rem;font-weight:700;color:var(--t1)">{{ $systemStats['ram_total'] ? $systemStats['ram_total'] . ' MB' : 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Used</div>
                <div id="live-ram-used" style="font-size:1.5rem;font-weight:700;color:var(--warn)">{{ $systemStats['ram_used'] ? $systemStats['ram_used'] . ' MB' : 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">Disk Used</div>
                <div id="live-disk-used" style="font-size:1.5rem;font-weight:700;color:var(--info)">-- GB</div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">Disk Free</div>
                <div id="live-disk-free" style="font-size:1.5rem;font-weight:700;color:var(--ok)">-- GB</div>
            </div>
        </div>
        <div style="padding:0 1.5rem 1.5rem 1.5rem; height:250px;">
            <canvas id="liveSystemChart"></canvas>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>

    <div class="table-card" style="margin-bottom:1.5rem">
        <div class="table-header">
            <div class="table-title">Docker Containers</div>
            <div style="font-size:0.75rem; color:var(--t3);">Live API Sync</div>
        </div>
        <div class="table-scroll">
            <table id="containersTable">
                <thead>
                    <tr>
                        <th style="width: 25%">Container Name</th>
                        <th style="width: 35%">Image</th>
                        <th style="width: 15%">State</th>
                        <th style="width: 25%">Status (Uptime)</th>
                    </tr>
                </thead>
                <tbody id="containersTbody">
                    <tr><td colspan="4" style="text-align:center; padding: 2rem; color: var(--t3);">Loading container data...</td></tr>
                </tbody>
            </table>
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
        // Email Activity Chart
        const ctxEmail = document.getElementById('emailChart').getContext('2d');
        new Chart(ctxEmail, {
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

        // Live System Resources Chart
        const ctxLive = document.getElementById('liveSystemChart').getContext('2d');
        const liveChart = new Chart(ctxLive, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'CPU Load',
                        data: [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'RAM Usage (MB)',
                        data: [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 400 // Smooth scrolling
                },
                plugins: {
                    legend: { labels: { color: '#9ca3af', font: { family: 'system-ui' } } }
                },
                scales: {
                    x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { beginAtZero: true, ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });

        // Live Polling
        const maxDataPoints = 30; // 30 points * 2 seconds = 60 seconds history

        function fetchLiveMetrics() {
            fetch('{{ route("admin.system-metrics") }}')
                .then(response => response.json())
                .then(data => {
                    // Update Text Elements
                    document.getElementById('live-cpu').innerText = data.cpu_load;
                    document.getElementById('live-ram-total').innerText = data.ram_total + ' MB';
                    document.getElementById('live-ram-used').innerText = data.ram_used + ' MB';
                    document.getElementById('live-disk-used').innerText = data.disk_used + ' GB';
                    document.getElementById('live-disk-free').innerText = data.disk_free + ' GB';

                    // Update Graph
                    const timeLabel = data.timestamp;
                    
                    // Push new data
                    liveChart.data.labels.push(timeLabel);
                    liveChart.data.datasets[0].data.push(data.cpu_load);
                    liveChart.data.datasets[1].data.push(data.ram_used);

                    // Remove old data to create scrolling effect
                    if (liveChart.data.labels.length > maxDataPoints) {
                        liveChart.data.labels.shift();
                        liveChart.data.datasets[0].data.shift();
                        liveChart.data.datasets[1].data.shift();
                    }

                    liveChart.update('quiet'); // Update without full animation for smoother stream

                    // Update Docker Containers Table
                    if (data.containers && data.containers.length > 0) {
                        const tbody = document.getElementById('containersTbody');
                        tbody.innerHTML = '';
                        data.containers.forEach(c => {
                            let stateBadge = '';
                            if (c.state === 'running') stateBadge = '<span class="badge badge-ok">Running</span>';
                            else if (c.state === 'exited') stateBadge = '<span class="badge badge-err">Exited</span>';
                            else stateBadge = `<span class="badge badge-warn">${c.state}</span>`;

                            tbody.innerHTML += `
                                <tr>
                                    <td style="font-weight: 500; color: var(--t1);">${c.name}</td>
                                    <td style="font-family: monospace; font-size: 0.8rem; color: var(--t2);">${c.image}</td>
                                    <td>${stateBadge}</td>
                                    <td style="font-size: 0.8rem; color: var(--t3);">${c.status}</td>
                                </tr>
                            `;
                        });
                    } else if (data.containers && data.containers.length === 0) {
                        const tbody = document.getElementById('containersTbody');
                        if (tbody.innerHTML.includes('Loading')) {
                            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem; color: var(--t3);">Docker socket not mounted. Check Installation Guide.</td></tr>';
                        }
                    }

                })
                .catch(err => console.error("Error fetching live metrics:", err));
        }

        // Start polling immediately and then every 2 seconds
        fetchLiveMetrics();
        setInterval(fetchLiveMetrics, 2000);
    </script>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">Recent Email Activity</div>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <div class="table-scroll">
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
        </div>

        <div class="table-card">
            <div class="table-header"><div class="table-title">Recent Users</div></div>
            <div class="table-scroll">
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
    </div>
@endsection
