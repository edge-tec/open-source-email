<?php

namespace App\Http\Controllers\Webmail;

use App\Http\Controllers\Controller;
use App\Services\Mail\ImapService;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) {
            return view('webmail.no-mailbox');
        }

        try {
            $imap = new ImapService($mailbox);
            $messages = $imap->getMessages('INBOX', $request->integer('page', 1), 25);
            $folders = $imap->getFolders();
            
            $defaults = ['Sent', 'Drafts', 'Trash', 'Junk'];
            $missing = array_diff($defaults, $folders);
            if (!empty($missing)) {
                foreach ($missing as $f) {
                    try { $imap->createFolder($f); } catch (\Exception $e) {}
                }
                $folders = $imap->getFolders();
            }

            $unread = $imap->getUnreadCount('INBOX');
        } catch (\Exception $e) {
            $messages = collect();
            $folders = [];
            $unread = 0;
            request()->session()->flash('error', 'IMAP Connection Failed: ' . $e->getMessage());
        }

        return view('webmail.inbox', compact('mailbox', 'messages', 'folders', 'unread'));
    }

    public function folder(Request $request, string $folder)
    {
        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) return redirect()->route('webmail.inbox');

        try {
            $imap = new ImapService($mailbox);
            $messages = $imap->getMessages($folder, $request->integer('page', 1), 25);
            $folders = $imap->getFolders();
            
            $defaults = ['Sent', 'Drafts', 'Trash', 'Junk'];
            $missing = array_diff($defaults, $folders);
            if (!empty($missing)) {
                foreach ($missing as $f) {
                    try { $imap->createFolder($f); } catch (\Exception $e) {}
                }
                $folders = $imap->getFolders();
            }

            $unread = $imap->getUnreadCount($folder);
        } catch (\Exception $e) {
            $messages = collect();
            $folders = [];
            $unread = 0;
            request()->session()->flash('error', 'IMAP Connection Failed: ' . $e->getMessage());
        }

        $currentFolder = $folder;
        return view('webmail.inbox', compact('mailbox', 'messages', 'folders', 'unread', 'currentFolder'));
    }

    public function search(Request $request)
    {
        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) return redirect()->route('webmail.inbox');

        $searchQuery = trim((string) $request->q);
        if (empty($searchQuery)) {
            return redirect()->route('webmail.inbox');
        }

        try {
            $imap = new ImapService($mailbox);
            $messages = $imap->search($searchQuery);
            $folders = $imap->getFolders();
        } catch (\Exception $e) {
            $messages = collect();
            $folders = [];
        }

        return view('webmail.inbox', compact('mailbox', 'messages', 'folders', 'searchQuery'));
    }

    public function bulk(Request $request)
    {
        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) return back();

        $action = $request->action;
        $uids = $request->uids ?? [];

        $folder = $request->folder ?? 'INBOX';

        if (!empty($uids)) {
            try {
                $imap = new ImapService($mailbox);
                foreach ($uids as $uid) {
                    if ($action === 'read') {
                        // Mark as Read
                        $imap->toggleRead($uid, $folder); 
                    } elseif ($action === 'unread') {
                        $imap->toggleRead($uid, $folder);
                    } elseif ($action === 'move_junk') {
                        $imap->moveMessage($uid, 'Junk', $folder);
                    } elseif ($action === 'move_trash') {
                        $imap->moveMessage($uid, 'Trash', $folder);
                    } elseif ($action === 'delete') {
                        $imap->deleteMessage($uid, $folder);
                    }
                }
            } catch (\Exception $e) {}
        }
        
        return back()->with('success', 'Bulk action applied.');
    }

    public function emptyTrash(Request $request)
    {
        $mailbox = $this->getActiveMailbox();
        if (!$mailbox) return back();

        try {
            $imap = new ImapService($mailbox);
            $imap->emptyFolder('Trash');
        } catch (\Exception $e) {}

        return back()->with('success', 'Trash cleared successfully.');
    }
}
