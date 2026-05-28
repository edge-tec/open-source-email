<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailServerSyncService;

class SyncMailServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edgemail:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all mailboxes and aliases to docker-mailserver configuration files';

    /**
     * Execute the console command.
     */
    public function handle(MailServerSyncService $syncService)
    {
        $this->info('Starting mail server synchronization...');
        
        $syncService->syncAll();
        
        $this->info('Synchronization complete!');
        $this->line('Note: Changes should be picked up automatically by the mail server.');
    }
}
