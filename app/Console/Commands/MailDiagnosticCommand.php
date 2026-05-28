<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mailbox;

class MailDiagnosticCommand extends Command
{
    protected $signature = 'mail:diagnose {email}';
    protected $description = 'Run a full diagnostic on SMTP and IMAP for a given mailbox';

    public function handle()
    {
        $email = $this->argument('email');
        $mailbox = Mailbox::where('email', $email)->first();

        if (!$mailbox) {
            $this->error("Mailbox $email not found in database.");
            return;
        }

        $password = $this->secret('Enter plain text password for this mailbox:');
        if (!$password) {
            $this->error("Password is required for IMAP diagnostic.");
            return;
        }

        $this->info("Starting Diagnostics for: $email");

        // Test SMTP
        $smtpHost = config('mail.mailers.smtp.host', '127.0.0.1');
        $smtpPort = config('mail.mailers.smtp.port', 587);
        $this->info("1. Testing SMTP Connection to $smtpHost:$smtpPort");
        
        $fp = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
        if (!$fp) {
            $this->error("SMTP Connection Failed: $errstr ($errno)");
        } else {
            $this->info("SMTP Connected!");
            $response = fgets($fp, 515);
            $this->line("<< " . trim($response));
            
            $this->line(">> EHLO localhost");
            fwrite($fp, "EHLO localhost\r\n");
            
            while($res = fgets($fp, 515)) {
                $this->line("<< " . trim($res));
                if (substr($res, 3, 1) == ' ') break;
            }
            
            $this->line(">> MAIL FROM:<{$email}>");
            fwrite($fp, "MAIL FROM:<{$email}>\r\n");
            $this->line("<< " . trim(fgets($fp, 515)));
            
            $this->line(">> RCPT TO:<{$email}>");
            fwrite($fp, "RCPT TO:<{$email}>\r\n");
            $this->line("<< " . trim(fgets($fp, 515)));
            
            $this->line(">> QUIT");
            fwrite($fp, "QUIT\r\n");
            fclose($fp);
        }

        // Test IMAP
        $imapHost = config('edgemail.imap.host', '127.0.0.1');
        $imapPort = config('edgemail.imap.port', 993);
        $encryption = config('edgemail.imap.encryption', 'ssl');
        
        $this->info("\n2. Testing IMAP Connection to $imapHost:$imapPort (Encryption: $encryption)");
        
        $flags = '/imap/notls';
        if ($encryption === 'ssl') {
            $flags = '/imap/ssl/novalidate-cert';
        } elseif ($encryption === 'tls') {
            $flags = '/imap/tls/novalidate-cert';
        }
        
        $ref = "{{$imapHost}:{$imapPort}{$flags}}INBOX";
        $this->line("Connection String: $ref");
        
        $imap = @imap_open($ref, $email, $password);
        if (!$imap) {
            $this->error("IMAP Connection Failed!");
            $this->error(print_r(imap_errors(), true));
        } else {
            $this->info("IMAP Connected Successfully!");
            $info = imap_check($imap);
            $this->info("Messages in INBOX: " . ($info->Nmsgs ?? 0));
            imap_close($imap);
        }
        
        $this->info("\nDiagnostics Complete.");
    }
}
