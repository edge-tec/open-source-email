<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Domains</div>
            <div class="stat-value"><?php echo e(number_format($stats['total_domains'])); ?></div>
            <div class="stat-change" style="color:var(--ok)"><?php echo e($stats['active_domains']); ?> active</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Mailboxes</div>
            <div class="stat-value"><?php echo e(number_format($stats['total_mailboxes'])); ?></div>
            <div class="stat-change" style="color:var(--ok)"><?php echo e($stats['active_mailboxes']); ?> active</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Emails Today</div>
            <div class="stat-value"><?php echo e(number_format($stats['emails_today'])); ?></div>
            <div class="stat-change">↑ <?php echo e($stats['emails_sent']); ?> sent · ↓ <?php echo e($stats['emails_received']); ?> received</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Spam Blocked</div>
            <div class="stat-value" style="color:var(--err)"><?php echo e(number_format($stats['spam_blocked'])); ?></div>
            <div class="stat-change" style="color:var(--t3)">Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Queue Size</div>
            <div class="stat-value" style="color:var(--warn)"><?php echo e(number_format($stats['queue_size'])); ?></div>
            <div class="stat-change">Pending delivery</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo e(number_format($stats['total_users'])); ?></div>
            <div class="stat-change" style="color:var(--ok)"><?php echo e($stats['active_users']); ?> active</div>
        </div>
    </div>

    <div class="table-card" style="margin-bottom:1.5rem">
        <div class="table-header"><div class="table-title">System Resources</div></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1.5rem;padding:1.5rem">
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">CPU Load (1m)</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--t1)"><?php echo e($systemStats['cpu_load']); ?></div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Total</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--t1)"><?php echo e($systemStats['ram_total'] ? $systemStats['ram_total'] . ' MB' : 'N/A'); ?></div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Used</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--warn)"><?php echo e($systemStats['ram_used'] ? $systemStats['ram_used'] . ' MB' : 'N/A'); ?></div>
            </div>
            <div>
                <div style="font-size:.8rem;color:var(--t2);margin-bottom:.25rem;text-transform:uppercase;font-weight:600">RAM Free</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--ok)"><?php echo e($systemStats['ram_free'] ? $systemStats['ram_free'] . ' MB' : 'N/A'); ?></div>
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
                labels: <?php echo json_encode($chartData['labels']); ?>,
                datasets: [
                    {
                        label: 'Sent',
                        data: <?php echo json_encode($chartData['sent']); ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Received',
                        data: <?php echo json_encode($chartData['received']); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Active Users',
                        data: <?php echo json_encode($chartData['active_users']); ?>,
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
                <a href="<?php echo e(route('admin.logs.index')); ?>" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <table>
                <thead><tr><th>Direction</th><th>From</th><th>To</th><th>Subject</th><th>Status</th><th>Time</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><span class="badge <?php echo e($log->direction === 'inbound' ? 'badge-info' : 'badge-ok'); ?>"><?php echo e($log->direction); ?></span></td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($log->sender, 25)); ?></td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($log->recipient, 25)); ?></td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($log->subject, 30)); ?></td>
                        <td><span class="badge <?php echo e($log->status === 'sent' || $log->status === 'delivered' ? 'badge-ok' : ($log->status === 'bounced' || $log->status === 'rejected' ? 'badge-err' : 'badge-warn')); ?>"><?php echo e($log->status); ?></span></td>
                        <td style="font-size:.75rem"><?php echo e($log->created_at?->diffForHumans()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--t3);padding:2rem">No email activity yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <div class="table-header"><div class="table-title">Recent Users</div></div>
            <table>
                <thead><tr><th>Name</th><th>Role</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div style="font-weight:500"><?php echo e($user->name); ?></div>
                            <div style="font-size:.7rem;color:var(--t3)"><?php echo e($user->email); ?></div>
                        </td>
                        <td><span class="badge badge-info"><?php echo e($user->role); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>