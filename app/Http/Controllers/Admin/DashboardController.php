<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\User;
use App\Models\EmailLog;
use App\Models\QueueLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_domains' => Domain::count(),
            'active_domains' => Domain::active()->count(),
            'total_mailboxes' => Mailbox::count(),
            'active_mailboxes' => Mailbox::active()->count(),
            'emails_today' => EmailLog::whereDate('created_at', today())->count(),
            'emails_sent' => EmailLog::outbound()->whereDate('created_at', today())->count(),
            'emails_received' => EmailLog::inbound()->whereDate('created_at', today())->count(),
            'spam_blocked' => EmailLog::where('is_spam', true)->whereDate('created_at', today())->count(),
            'queue_size' => QueueLog::whereIn('status', ['queued', 'active', 'deferred'])->count(),
            'storage_used' => Mailbox::sum('used_quota'),
        ];

        $recentLogs = EmailLog::latest()->limit(10)->get();
        $recentUsers = User::latest()->limit(5)->get();

        $chartData = ['labels' => [], 'sent' => [], 'received' => [], 'active_users' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = now()->subDays($i)->format('M d');
            $chartData['sent'][] = EmailLog::outbound()->whereDate('created_at', $date)->count();
            $chartData['received'][] = EmailLog::inbound()->whereDate('created_at', $date)->count();
            $chartData['active_users'][] = User::whereDate('last_login_at', $date)->count();
        }

        // System Metrics
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0];
        $cpuLoad = $load[0] ?? 0;
        
        $ramTotal = 0; $ramUsed = 0; $ramFree = 0;
        $freeOutput = @shell_exec('free -m 2>/dev/null');
        if ($freeOutput) {
            $lines = explode("\n", trim($freeOutput));
            if (isset($lines[1])) {
                $parts = preg_split('/\s+/', $lines[1]);
                $ramTotal = (int)($parts[1] ?? 0);
                $ramUsed = (int)($parts[2] ?? 0);
                $ramFree = (int)($parts[3] ?? 0);
            }
        }
        $systemStats = [
            'cpu_load' => round($cpuLoad, 2),
            'ram_total' => $ramTotal,
            'ram_used' => $ramUsed,
            'ram_free' => $ramFree,
        ];

        return view('admin.dashboard', compact('stats', 'recentLogs', 'recentUsers', 'chartData', 'systemStats'));
    }

    public function metrics()
    {
        // CPU Load
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0];
        $cpuLoad = $load[0] ?? 0;
        
        // RAM
        $ramTotal = 0; $ramUsed = 0; $ramFree = 0;
        $freeOutput = @shell_exec('free -m 2>/dev/null');
        if ($freeOutput) {
            $lines = explode("\n", trim($freeOutput));
            if (isset($lines[1])) {
                $parts = preg_split('/\s+/', $lines[1]);
                $ramTotal = (int)($parts[1] ?? 0);
                $ramUsed = (int)($parts[2] ?? 0);
                $ramFree = (int)($parts[3] ?? 0);
            }
        }

        // Disk Space (in GB)
        $diskTotalBytes = @disk_total_space('/');
        $diskFreeBytes = @disk_free_space('/');
        $diskTotal = $diskTotalBytes ? round($diskTotalBytes / 1073741824, 2) : 0;
        $diskFree = $diskFreeBytes ? round($diskFreeBytes / 1073741824, 2) : 0;
        $diskUsed = $diskTotal - $diskFree;

        // Docker Containers
        $containers = [];
        if (file_exists('/var/run/docker.sock')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
            curl_setopt($ch, CURLOPT_URL, "http://localhost/containers/json?all=1");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $dockerData = json_decode($response, true);
                if (is_array($dockerData)) {
                    foreach ($dockerData as $c) {
                        $containers[] = [
                            'name' => ltrim($c['Names'][0] ?? 'Unknown', '/'),
                            'image' => $c['Image'] ?? 'Unknown',
                            'state' => $c['State'] ?? 'unknown',
                            'status' => $c['Status'] ?? 'Unknown'
                        ];
                    }
                }
            }
        }

        return response()->json([
            'cpu_load' => round($cpuLoad, 2),
            'ram_total' => $ramTotal,
            'ram_used' => $ramUsed,
            'ram_free' => $ramFree,
            'disk_total' => $diskTotal,
            'disk_used' => $diskUsed,
            'disk_free' => $diskFree,
            'containers' => $containers,
            'timestamp' => now()->format('H:i:s')
        ]);
    }
}
