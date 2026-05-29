<?php

namespace App\Services\Mail;

use App\Models\Mailbox;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SmtpService
{
    protected Mailbox $mailbox;

    public function __construct(Mailbox $mailbox)
    {
        $this->mailbox = $mailbox;
    }

    public function send(array $data): bool
    {
        $to = $this->parseRecipients($data['to']);
        $cc = isset($data['cc']) ? $this->parseRecipients($data['cc']) : [];
        $bcc = isset($data['bcc']) ? $this->parseRecipients($data['bcc']) : [];

        $password = session()->has('webmail_password') ? decrypt(session('webmail_password')) : $this->mailbox->password;

        // Dynamically configure the SMTP mailer to authenticate as the current mailbox user.
        // Disable SSL verification to allow sending via self-signed or Let's Encrypt certs on internal network.
        config([
            'mail.mailers.smtp.username' => $this->mailbox->email,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        ]);
        
        // Force mail manager to re-resolve the mailer with the new config
        app('mail.manager')->purge('smtp');

        Mail::raw($data['is_html'] ?? false ? '' : $data['body'], function (Message $message) use ($data, $to, $cc, $bcc) {
            $message->from($this->mailbox->email, $this->mailbox->name ?? $this->mailbox->local_part);
            $message->subject($data['subject']);

            foreach ($to as $recipient) {
                $message->to($recipient['email'], $recipient['name']);
            }
            foreach ($cc as $recipient) {
                $message->cc($recipient['email'], $recipient['name']);
            }
            foreach ($bcc as $recipient) {
                $message->bcc($recipient['email'], $recipient['name']);
            }

            if (!empty($data['is_html'])) {
                $message->html($data['body']);
            }

            // Handle attachments
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (is_object($attachment) && method_exists($attachment, 'getRealPath')) {
                        $message->attach($attachment->getRealPath(), [
                            'as' => $attachment->getClientOriginalName(),
                            'mime' => $attachment->getMimeType(),
                        ]);
                    }
                }
            }
        });

        return true;
    }

    protected function parseRecipients(string $recipients): array
    {
        $parsed = [];
        $parts = array_map('trim', explode(',', $recipients));
        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            if (preg_match('/^(.*?)\s*<([^>]+)>$/', $part, $matches)) {
                $name = trim($matches[1], ' "');
                $email = trim($matches[2]);
                $parsed[] = ['email' => $email, 'name' => $name];
            } else {
                $parsed[] = ['email' => $part, 'name' => null];
            }
        }
        return $parsed;
    }
}
