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
        $mailbox = auth()->user()->mailboxes()->active()->first();
        $replyTo = $request->only(['to', 'subject', 'body', 'reply_uid']);
        return view('webmail.compose', compact('mailbox', 'replyTo'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
        ]);

        $mailbox = auth()->user()->mailboxes()->active()->first();
        if (!$mailbox) return back()->withErrors(['No active mailbox found.']);

        try {
            if ($request->input('action') === 'draft') {
                $email = (new \Symfony\Component\Mime\Email())
                    ->from($mailbox->email)
                    ->to($request->to)
                    ->subject($request->subject)
                    ->html($request->body);
                
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
}
