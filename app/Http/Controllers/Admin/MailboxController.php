<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use App\Models\Domain;
use App\Services\MailServerSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MailboxController extends Controller
{
    public function index(Request $request)
    {
        $mailboxes = Mailbox::with('domain')
            ->when($request->search, fn($q) => $q->where('email', 'like', "%{$request->search}%"))
            ->when($request->domain_id, fn($q) => $q->where('domain_id', $request->domain_id))
            ->latest()
            ->paginate(15);

        $domains = Domain::active()->get();
        return view('admin.mailboxes.index', compact('mailboxes', 'domains'));
    }

    public function create()
    {
        $domains = Domain::active()->get();
        return view('admin.mailboxes.create', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'local_part' => 'required|string|max:255',
            'domain_id' => 'required|exists:domains,id',
            'password' => 'required|string|min:8',
            'quota' => 'integer|min:1',
        ]);

        $domain = Domain::findOrFail($request->domain_id);
        $email = strtolower($request->local_part) . '@' . $domain->domain;

        $existingMailbox = Mailbox::withTrashed()
            ->where('domain_id', $domain->id)
            ->where('local_part', strtolower($request->local_part))
            ->first();

        if ($existingMailbox && !$existingMailbox->trashed()) {
            return back()->withErrors(['local_part' => 'This mailbox already exists.']);
        }

        // Check domain mailbox limit (only if we are actually adding a new active mailbox)
        if ($domain->mailboxes()->count() >= $domain->max_mailboxes) {
            return back()->withErrors(['domain_id' => 'Domain has reached maximum mailbox limit.']);
        }

        if ($existingMailbox && $existingMailbox->trashed()) {
            $existingMailbox->restore();
            $existingMailbox->update([
                'user_id' => auth()->id(),
                'email' => $email, // Update email in case domain name was changed
                'password' => Hash::make($request->password),
                'name' => $request->name,
                'quota' => $request->quota ?? 1024,
                'status' => 'active',
                'maildir' => $domain->domain . '/' . strtolower($request->local_part) . '/Maildir/',
            ]);
            $mailbox = $existingMailbox;
        } else {
            $mailbox = Mailbox::create([
                'user_id' => auth()->id(),
                'domain_id' => $domain->id,
                'local_part' => strtolower($request->local_part),
                'email' => $email,
                'password' => Hash::make($request->password),
                'name' => $request->name,
                'quota' => $request->quota ?? 1024,
                'status' => 'active',
                'maildir' => $domain->domain . '/' . strtolower($request->local_part) . '/Maildir/',
            ]);
        }

        app(MailServerSyncService::class)->syncAccounts();

        return redirect()->route('admin.mailboxes.index')->with('success', 'Mailbox created.');
    }

    public function edit(Mailbox $mailbox)
    {
        $domains = Domain::active()->get();
        return view('admin.mailboxes.edit', compact('mailbox', 'domains'));
    }

    public function update(Request $request, Mailbox $mailbox)
    {
        $data = $request->only(['name', 'quota', 'status', 'signature', 'is_catchall', 'send_only']);
        if ($request->filled('password')) {
            $mailbox->password = Hash::make($request->password);
        }

        $mailbox->update($data);

        app(MailServerSyncService::class)->syncAccounts();
        return redirect()->route('admin.mailboxes.index')->with('success', 'Mailbox updated.');
    }

    public function destroy(Mailbox $mailbox)
    {
        $mailbox->delete();
        app(MailServerSyncService::class)->syncAccounts();
        return redirect()->route('admin.mailboxes.index')->with('success', 'Mailbox deleted.');
    }

    public function webmailLogin(Mailbox $mailbox)
    {
        // We cannot automatically log into IMAP because we only store bcrypt hashes
        // of the password, and IMAP requires the plain-text password.
        // Redirect the admin to the webmail login page with the email pre-filled.
        return redirect()->route('webmail.login')->withInput(['email' => $mailbox->email])
            ->with('info', 'For IMAP connectivity, please enter the mailbox password. We do not store plain-text passwords for security.');
    }
}
