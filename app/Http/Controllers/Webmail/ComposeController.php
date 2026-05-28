<?php

namespace App\Http\Controllers\Webmail;

use App\Http\Controllers\Controller;
use App\Services\Mail\SmtpService;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class ComposeController extends Controller
{
    public function create(Request $request)
    {
        $mailbox = $this->getActiveMailbox();
        $replyTo = $request->only(['to', 'subject', 'body', 'reply_uid', 'forward_uid', 'folder']);

        if ($request->has('reply_uid') || $request->has('forward_uid')) {
            try {
                $imap = new \App\Services\Mail\ImapService($mailbox);
                $uid = $request->input('reply_uid') ?? $request->input('forward_uid');
                $folder = $request->input('folder', 'INBOX');
                $message = $imap->getMessage($uid, $folder);
                
                $date = date('M d, Y, h:i A', strtotime($message['date']));
                $quoteHeader = $request->has('reply_uid') 
                    ? "\n\nOn {$date}, {$message['from']} wrote:\n"
                    : "\n\n---------- Forwarded message ---------\nFrom: {$message['from']}\nDate: {$date}\nSubject: {$message['subject']}\nTo: {$message['to']}\n\n";
                
                $originalBody = $message['body'] ?? '';
                $replyTo['body'] = nl2br(htmlspecialchars($quoteHeader)) . "<blockquote style='border-left:2px solid #ccc;margin-left:10px;padding-left:10px'>" . $originalBody . "</blockquote>";
            } catch (\Exception $e) {
                // Ignore error and leave body empty
            }
        }

        return view('webmail.compose', compact('mailbox', 'replyTo'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
        ]);

        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) return back()->withErrors(['No active mailbox found.']);

        try {
            if ($request->input('action') === 'draft') {
                $email = (new \Symfony\Component\Mime\Email())
                    ->from($mailbox->email)
                    ->subject($request->subject)
                    ->html($request->body);
                
                foreach ($this->parseRecipients($request->to) as $addr) {
                    $email->addTo($addr);
                }
                if ($request->cc) {
                    foreach ($this->parseRecipients($request->cc) as $addr) {
                        $email->addCc($addr);
                    }
                }
                if ($request->bcc) {
                    foreach ($this->parseRecipients($request->bcc) as $addr) {
                        $email->addBcc($addr);
                    }
                }
                
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $email->attachFromPath($file->getRealPath(), $file->getClientOriginalName(), $file->getClientMimeType());
                    }
                }

                $imap = new \App\Services\Mail\ImapService($mailbox);
                $imap->appendMessage('Drafts', $email->toString(), '\\Draft');

                return redirect()->route('webmail.inbox')->with('success', 'Saved as draft.');
            }

            $smtp = new SmtpService($mailbox);
            $result = $smtp->send([
                'to' => $request->to,
                'cc' => $request->cc,
                'bcc' => $request->bcc,
                'subject' => $request->subject,
                'body' => $request->body,
                'is_html' => true,
                'attachments' => $request->file('attachments', []),
            ]);

            // Append to Sent folder
            try {
                $email = (new \Symfony\Component\Mime\Email())
                    ->from($mailbox->email)
                    ->subject($request->subject)
                    ->html($request->body);
                
                foreach ($this->parseRecipients($request->to) as $addr) {
                    $email->addTo($addr);
                }
                if ($request->cc) {
                    foreach ($this->parseRecipients($request->cc) as $addr) {
                        $email->addCc($addr);
                    }
                }
                if ($request->bcc) {
                    foreach ($this->parseRecipients($request->bcc) as $addr) {
                        $email->addBcc($addr);
                    }
                }
                
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $email->attachFromPath($file->getRealPath(), $file->getClientOriginalName(), $file->getClientMimeType());
                    }
                }
                $imap = new \App\Services\Mail\ImapService($mailbox);
                $imap->appendMessage('Sent', $email->toString(), '\\Seen');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to append to Sent folder: " . $e->getMessage());
            }

            // Log the sent email
            EmailLog::create([
                'mailbox_id' => $mailbox->id,
                'domain_id' => $mailbox->domain_id,
                'direction' => 'outbound',
                'sender' => $mailbox->email,
                'recipient' => $request->to,
                'subject' => $request->subject,
                'status' => 'sent',
                'has_attachment' => $request->hasFile('attachments'),
            ]);

            return redirect()->route('webmail.inbox')->with('success', 'Email sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['Failed to process email: ' . $e->getMessage()])->withInput();
        }
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate(['file' => 'required|file|max:25600']); // 25MB
        $path = $request->file('file')->store('attachments', 'local');
        return response()->json(['path' => $path, 'name' => $request->file('file')->getClientOriginalName()]);
    }

    private function parseRecipients($recipients) {
        $parsed = [];
        $parts = array_map('trim', explode(',', $recipients));
        foreach ($parts as $part) {
            if (empty($part)) continue;
            if (preg_match('/^(.*?)\s*<([^>]+)>$/', $part, $matches)) {
                $name = trim($matches[1], ' "');
                $email = trim($matches[2]);
                $parsed[] = new \Symfony\Component\Mime\Address($email, $name);
            } else {
                $parsed[] = new \Symfony\Component\Mime\Address($part);
            }
        }
        return $parsed;
    }
}
