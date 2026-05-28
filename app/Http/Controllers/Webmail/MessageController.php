<?php
namespace App\Http\Controllers\Webmail;
use App\Http\Controllers\Controller;
use App\Services\Mail\ImapService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function show(Request $request, $uid) {
        $mailbox = auth()->user()->mailboxes()->active()->first();
        $folder = $request->query('folder', 'INBOX');
        try {
            $imap = new ImapService($mailbox);
            $message = $imap->getMessage($uid, $folder);
            $folders = $imap->getFolders();
        } catch (\Exception $e) {
            return redirect()->route('webmail.inbox')->withErrors(['Could not load message: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
        }
        return view('webmail.read', compact('mailbox', 'message', 'folders', 'folder'));
    }

    public function reply(Request $request, $uid) {
        $request->validate(['body' => 'required']);
        return redirect()->route('webmail.compose', ['reply_uid' => $uid, 'to' => $request->reply_to, 'subject' => 'Re: ' . $request->subject]);
    }

    public function forward(Request $request, $uid) {
        return redirect()->route('webmail.compose', ['to' => $request->to, 'subject' => 'Fwd: ' . $request->subject]);
    }

    public function move(Request $request, $uid) {
        $mailbox = auth()->user()->mailboxes()->active()->first();
        $sourceFolder = $request->input('source_folder', 'INBOX');
        try {
            $imap = new ImapService($mailbox);
            $imap->moveMessage($uid, $request->folder, $sourceFolder);
        } catch (\Exception $e) {}
        return back()->with('success', 'Message moved.');
    }

    public function toggleRead(Request $request, $uid) {
        $mailbox = auth()->user()->mailboxes()->active()->first();
        $folder = $request->input('source_folder', 'INBOX');
        try {
            $imap = new ImapService($mailbox);
            $imap->toggleRead($uid, $folder);
        } catch (\Exception $e) {}
        return back();
    }

    public function destroy(Request $request, $uid) {
        $mailbox = auth()->user()->mailboxes()->active()->first();
        $folder = $request->input('source_folder', 'INBOX');
        try {
            $imap = new ImapService($mailbox);
            if (strcasecmp($folder, 'Trash') === 0) {
                $imap->deleteMessage($uid, $folder);
            } else {
                $imap->moveMessage($uid, 'Trash', $folder);
            }
        } catch (\Exception $e) {}
        return redirect()->route('webmail.inbox')->with('success', 'Message deleted.');
    }
}
