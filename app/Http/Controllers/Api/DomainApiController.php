<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DkimKey;
use Illuminate\Http\Request;

class DomainApiController extends Controller
{
    public function index(Request $request) {
        $domains = Domain::when(!$request->user()->isSuperAdmin(), fn($q) => $q->where('user_id', $request->user()->id))
            ->withCount('mailboxes', 'aliases')->paginate(15);
        return response()->json($domains);
    }
    public function store(Request $request) {
        $request->validate(['domain' => 'required|string|unique:domains,domain']);
        $domain = Domain::create([
            'user_id' => $request->user()->id,
            'domain' => strtolower($request->domain),
            'description' => $request->description,
            'status' => 'active',
            'max_mailboxes' => $request->max_mailboxes ?? 10,
            'max_aliases' => $request->max_aliases ?? 50,
            'max_quota' => $request->max_quota ?? 10240,
            'transport' => 'virtual',
        ]);
        return response()->json($domain, 201);
    }
    public function show(Domain $domain) { return response()->json($domain->load('mailboxes', 'aliases')); }
    public function update(Request $request, Domain $domain) {
        $domain->update($request->only(['description', 'status', 'max_mailboxes', 'max_aliases', 'max_quota', 'catch_all']));
        return response()->json($domain);
    }
    public function destroy(Domain $domain) { $domain->delete(); return response()->json(null, 204); }
    public function generateDkim(Domain $domain) {
        $keyPair = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($keyPair, $privateKey);
        $publicKey = openssl_pkey_get_details($keyPair)['key'];
        $selector = config('mailserver.dkim.selector', 'edgemail');
        DkimKey::updateOrCreate(['domain_id' => $domain->id, 'selector' => $selector], [
            'private_key' => $privateKey, 'public_key' => $publicKey, 'key_bits' => 2048, 'status' => 'active',
        ]);
        $domain->update(['dkim_enabled' => true, 'dkim_selector' => $selector]);
        return response()->json(['message' => 'DKIM key generated.', 'public_key' => $publicKey]);
    }
}
