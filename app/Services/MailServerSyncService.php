<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\Alias;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MailServerSyncService
{
    /**
     * Sync both accounts and aliases.
     */
    public function syncAll(): void
    {
        $this->syncAccounts();
        $this->syncAliases();
        $this->syncSieveScripts();
    }

    /**
     * Generate postfix-accounts.cf
     */
    public function syncAccounts(): void
    {
        $path = base_path('docker/mailserver/config/postfix-accounts.cf');
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $mailboxes = Mailbox::where('status', 'active')->get();
        $content = "";

        foreach ($mailboxes as $mailbox) {
            // Laravel uses Bcrypt ($2y$). We prepend {BLF-CRYPT} so Dovecot understands it.
            $content .= "{$mailbox->email}|{BLF-CRYPT}{$mailbox->password}\n";
        }

        File::put($path, $content);
        Log::info("MailServerSyncService: Synced " . $mailboxes->count() . " mailboxes to postfix-accounts.cf");
    }

    /**
     * Generate postfix-virtual.cf
     */
    public function syncAliases(): void
    {
        $path = base_path('docker/mailserver/config/postfix-virtual.cf');
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $aliases = Alias::where('status', 'active')->get();
        $content = "";

        foreach ($aliases as $alias) {
            $content .= "{$alias->source}|{$alias->destination}\n";
        }

        File::put($path, $content);
        Log::info("MailServerSyncService: Synced " . $aliases->count() . " aliases to postfix-virtual.cf");
    }

    /**
     * Generate Sieve scripts for each mailbox.
     */
    public function syncSieveScripts(): void
    {
        $generator = new SieveScriptGenerator();
        $mailboxes = Mailbox::with(['forwardingRules', 'autoresponder'])->where('status', 'active')->get();
        
        $synced = 0;
        foreach ($mailboxes as $mailbox) {
            $script = $generator->generate($mailbox);
            if (empty(trim($script))) {
                continue;
            }

            // Path to user's maildir: /var/vmail/domain/local_part/.dovecot.sieve
            // Note: Maildir path format comes from MailServerConfigurator query
            $domain = $mailbox->domain->domain ?? null;
            if (!$domain) continue;

            $dir = "/var/vmail/{$domain}/{$mailbox->local_part}";
            if (!File::isDirectory($dir)) {
                // The directory might not exist until the first email arrives, 
                // but we can create the structure if we need to set the rule proactively.
                @mkdir($dir, 0755, true);
                @chown($dir, 5000);
                @chgrp($dir, 5000);
            }

            $path = "{$dir}/.dovecot.sieve";
            File::put($path, $script);
            @chown($path, 5000);
            @chgrp($path, 5000);
            $synced++;
        }

        Log::info("MailServerSyncService: Synced Sieve scripts for {$synced} mailboxes.");
    }
}
