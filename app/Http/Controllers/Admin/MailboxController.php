<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use App\Models\Domain;
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

        // Check uniqueness
        if (Mailbox::where('email', $email)->exists()) {
            return back()->withErrors(['local_part' => 'This mailbox already exists.']);
        }

        // Check domain mailbox limit
        if ($domain->mailboxes()->count() >= $domain->max_mailboxes) {
            return back()->withErrors(['domain_id' => 'Domain has reached maximum mailbox limit.']);
        }

        Mailbox::create([
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
            $data['password'] = Hash::make($request->password);
        }
        $mailbox->update($data);
        return redirect()->route('admin.mailboxes.index')->with('success', 'Mailbox updated.');
    }

    public function destroy(Mailbox $mailbox)
    {
        $mailbox->delete();
        return redirect()->route('admin.mailboxes.index')->with('success', 'Mailbox deleted.');
    }

    public function webmailLogin(Mailbox $mailbox)
    {
        session(['active_mailbox_id' => $mailbox->id]);
        return redirect()->route('webmail.inbox');
    }
}
