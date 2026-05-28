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

        // Disable SSL verification to allow sending via self-signed or Let's Encrypt certs on internal network
        config([
            'mail.mailers.smtp.stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        ]);
        
        // Force mail manager to re-resolve the mailer with the new config
        app('mail.manager')->forget('smtp');

        Mail::raw($data['is_html'] ?? false ? '' : $data['body'], function (Message $message) use ($data, $to, $cc, $bcc) {
            $message->from($this->mailbox->email, $this->mailbox->name ?? $this->mailbox->local_part);
            $message->subject($data['subject']);

            foreach ($to as $recipient) {
                $message->to($recipient);
            }
            foreach ($cc as $recipient) {
                $message->cc($recipient);
            }
            foreach ($bcc as $recipient) {
                $message->bcc($recipient);
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
        return array_map('trim', explode(',', $recipients));
    }
}
