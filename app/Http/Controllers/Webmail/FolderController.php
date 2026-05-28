<?php
namespace App\Http\Controllers\Webmail;
use App\Http\Controllers\Controller;
use App\Services\Mail\ImapService;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index() {
        $mailbox = auth()->user()->getActiveMailbox();
        try {
            $imap = new ImapService($mailbox);
            $folders = $imap->getFolders();
        } catch (\Exception $e) { $folders = []; }
        return response()->json($folders);
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:100']);
        $mailbox = auth()->user()->getActiveMailbox();
        try {
            $imap = new ImapService($mailbox);
            $imap->createFolder($request->name);
        } catch (\Exception $e) {
            return back()->withErrors(['Could not create folder.']);
        }
        return back()->with('success', 'Folder created.');
    }

    public function destroy($folder) {
        $mailbox = auth()->user()->getActiveMailbox();
        try {
            $imap = new ImapService($mailbox);
            $imap->deleteFolder($folder);
        } catch (\Exception $e) {}
        return back()->with('success', 'Folder deleted.');
    }
}
