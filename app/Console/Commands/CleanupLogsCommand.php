<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Models\QueueLog;
use App\Models\FailedLogin;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupLogsCommand extends Command
{
    protected $signature = 'edgemail:cleanup-logs {--days=30 : Number of days to retain logs}';
    protected $description = 'Clean up old email logs, queue logs, and failed logins';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Cleaning up logs older than {$days} days...");

        $emailLogs = EmailLog::where('created_at', '<', $cutoff)->delete();
        $this->line("- Deleted {$emailLogs} old email logs.");

        // For queue logs, only delete finished ones (sent, bounced, cancelled), keep active/deferred if they are still trying
        $queueLogs = QueueLog::where('created_at', '<', $cutoff)
            ->whereIn('status', ['sent', 'bounced', 'cancelled'])
            ->delete();
        $this->line("- Deleted {$queueLogs} old queue logs.");

        $failedLogins = FailedLogin::where('created_at', '<', $cutoff)->delete();
        $this->line("- Deleted {$failedLogins} old failed login records.");

        $this->info('Cleanup completed successfully.');
    }
}
