<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MailboxApiController extends Controller
{
    public function index(Request $request) {
        $mailboxes = Mailbox::with('domain')
            ->when($request->domain_id, fn($q) => $q->where('domain_id', $request->domain_id))
            ->paginate(15);
        return response()->json($mailboxes);
    }
    public function store(Request $request) {
        $request->validate(['local_part' => 'required', 'domain_id' => 'required|exists:domains,id', 'password' => 'required|min:8']);
        $domain = Domain::findOrFail($request->domain_id);
        $email = strtolower($request->local_part) . '@' . $domain->domain;
        if (Mailbox::where('email', $email)->exists()) return response()->json(['message' => 'Mailbox already exists.'], 422);

        $mailbox = Mailbox::create([
            'user_id' => $request->user()->id, 'domain_id' => $domain->id,
            'local_part' => strtolower($request->local_part), 'email' => $email,
            'password' => Hash::make($request->password), 'name' => $request->name,
            'quota' => $request->quota ?? 1024, 'status' => 'active',
            'maildir' => $domain->domain . '/' . strtolower($request->local_part) . '/Maildir/',
        ]);
        return response()->json($mailbox, 201);
    }
    public function show(Mailbox $mailbox) { return response()->json($mailbox->load('domain')); }
    public function update(Request $request, Mailbox $mailbox) {
        $data = $request->only(['name', 'quota', 'status', 'signature']);
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $mailbox->update($data);
        return response()->json($mailbox);
    }
    public function destroy(Mailbox $mailbox) { $mailbox->delete(); return response()->json(null, 204); }
}
