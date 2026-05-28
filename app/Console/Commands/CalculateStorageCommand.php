<?php

namespace App\Console\Commands;

use App\Models\Mailbox;
use App\Models\StorageUsage;
use Illuminate\Console\Command;
use App\Services\Mail\ImapService;

class CalculateStorageCommand extends Command
{
    protected $signature = 'edgemail:calculate-storage';
    protected $description = 'Calculate storage usage for all mailboxes';

    public function handle()
    {
        $mailboxes = Mailbox::active()->get();
        $bar = $this->output->createProgressBar(count($mailboxes));

        $this->info('Calculating mailbox storage usage...');

        foreach ($mailboxes as $mailbox) {
            try {
                $dir = config('edgemail.mail_dir', '/var/vmail/') . $mailbox->getMaildirPath();
                
                $usedBytes = 0;
                $messageCount = 0;

                // Basic recursive directory size calculation if the directory exists
                if (is_dir($dir)) {
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $usedBytes += $file->getSize();
                            // Count actual emails (usually files not starting with dot)
                            if (strpos($file->getFilename(), '.') !== 0) {
                                $messageCount++;
                            }
                        }
                    }
                }

                $quotaBytes = $mailbox->quota * 1048576; // MB to Bytes
                $percentage = $quotaBytes > 0 ? ($usedBytes / $quotaBytes) * 100 : 0;

                StorageUsage::updateOrCreate(
                    ['mailbox_id' => $mailbox->id],
                    [
                        'domain_id' => $mailbox->domain_id,
                        'used_bytes' => $usedBytes,
                        'message_count' => $messageCount,
                        'quota_bytes' => $quotaBytes,
                        'percentage_used' => $percentage,
                        'last_calculated_at' => now(),
                    ]
                );

                // Update the mailbox directly as well for fast access
                $mailbox->update([
                    'used_quota' => round($usedBytes / 1048576, 2),
                    'msg_count' => $messageCount,
                ]);

            } catch (\Exception $e) {
                $this->error("Failed for mailbox {$mailbox->email}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Storage calculation completed.');
    }
}
