<?php

namespace App\Services\Installer;

class CronJobInstaller
{
    /**
     * Get all required cron entries for EdgeMail.
     */
    public function getCronEntries(): array
    {
        $phpPath = PHP_BINARY;
        $artisan = base_path('artisan');

        return [
            [
                'schedule' => '* * * * *',
                'command' => "cd " . base_path() . " && {$phpPath} {$artisan} schedule:run >> /dev/null 2>&1",
                'description' => 'Laravel scheduler (runs every minute)',
            ],
            [
                'schedule' => '*/5 * * * *',
                'command' => "cd " . base_path() . " && {$phpPath} {$artisan} edgemail:calculate-storage >> /dev/null 2>&1",
                'description' => 'Storage usage calculation (every 5 minutes)',
            ],
            [
                'schedule' => '0 2 * * *',
                'command' => "cd " . base_path() . " && {$phpPath} {$artisan} edgemail:cleanup-logs --days=30 >> /dev/null 2>&1",
                'description' => 'Log cleanup (daily at 2 AM)',
            ],
            [
                'schedule' => '0 0,12 * * *',
                'command' => 'certbot renew --quiet --post-hook "systemctl reload postfix dovecot nginx"',
                'description' => 'SSL certificate renewal check (twice daily)',
            ],
        ];
    }

    /**
     * Generate the crontab content.
     */
    public function generateCrontab(): string
    {
        $entries = $this->getCronEntries();
        $content = "# EdgeMail Cron Jobs\n# Generated automatically\n\n";

        foreach ($entries as $entry) {
            $content .= "# {$entry['description']}\n";
            $content .= "{$entry['schedule']} {$entry['command']}\n\n";
        }

        return $content;
    }

    /**
     * Get supervisor configuration for queue workers.
     */
    public function getSupervisorConfig(): string
    {
        $phpPath = PHP_BINARY;
        $artisan = base_path('artisan');
        $basePath = base_path();

        return <<<CONF
[program:edgemail-worker]
process_name=%(program_name)s_%(process_num)02d
command={$phpPath} {$artisan} queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile={$basePath}/storage/logs/worker.log
stopwaitsecs=3600
CONF;
    }
}
