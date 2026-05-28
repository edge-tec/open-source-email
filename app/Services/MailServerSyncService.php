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
}
