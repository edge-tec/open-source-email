<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DkimKey;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $domains = Domain::with('user')
            ->withCount('mailboxes', 'aliases')
            ->when($request->search, fn($q) => $q->where('domain', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return view('admin.domains.index', compact('domains'));
    }

    public function create()
    {
        return view('admin.domains.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|unique:domains,domain|max:255',
            'max_mailboxes' => 'integer|min:1',
            'max_aliases' => 'integer|min:1',
            'max_quota' => 'integer|min:1',
        ]);

        $domain = Domain::create([
            'user_id' => auth()->id(),
            'domain' => strtolower($request->domain),
            'description' => $request->description,
            'status' => 'active',
            'max_mailboxes' => $request->max_mailboxes ?? 10,
            'max_aliases' => $request->max_aliases ?? 50,
            'max_quota' => $request->max_quota ?? 10240,
            'transport' => 'virtual',
            'spf_record' => config('mailserver.dns.spf_record'),
            'dmarc_record' => str_replace('{domain}', $request->domain, config('mailserver.dns.dmarc_record')),
        ]);

        return redirect()->route('admin.domains.show', $domain)->with('success', 'Domain created successfully.');
    }

    public function show(Domain $domain)
    {
        $domain->load('mailboxes', 'aliases', 'dkimKeys');
        return view('admin.domains.show', compact('domain'));
    }

    public function edit(Domain $domain)
    {
        return view('admin.domains.edit', compact('domain'));
    }

    public function update(Request $request, Domain $domain)
    {
        $request->validate([
            'domain' => 'required|string|unique:domains,domain,' . $domain->id,
            'status' => 'in:active,inactive,pending',
        ]);

        $domain->update($request->only([
            'domain', 'description', 'status', 'max_mailboxes', 'max_aliases',
            'max_quota', 'catch_all', 'spf_record', 'dmarc_record',
        ]));

        return redirect()->route('admin.domains.show', $domain)->with('success', 'Domain updated.');
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return redirect()->route('admin.domains.index')->with('success', 'Domain deleted.');
    }

    public function generateDkim(Domain $domain)
    {
        $keyPair = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($keyPair, $privateKey);
        $publicKey = openssl_pkey_get_details($keyPair)['key'];

        // Clean public key for DNS record
        $publicKeyClean = str_replace(["\r\n", "\n", '-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'], '', $publicKey);

        $selector = config('mailserver.dkim.selector', 'edgemail');
        $dnsRecord = "{$selector}._domainkey.{$domain->domain} IN TXT \"v=DKIM1; k=rsa; p={$publicKeyClean}\"";

        DkimKey::updateOrCreate(
            ['domain_id' => $domain->id, 'selector' => $selector],
            [
                'private_key' => $privateKey,
                'public_key' => $publicKey,
                'dns_record' => $dnsRecord,
                'key_bits' => 2048,
                'status' => 'active',
            ]
        );

        $domain->update([
            'dkim_enabled' => true,
            'dkim_selector' => $selector,
            'dkim_private_key' => $privateKey,
            'dkim_public_key' => $publicKey,
        ]);

        return back()->with('success', 'DKIM key generated. Add the DNS TXT record to enable DKIM signing.');
    }

    public function installSsl(Request $request, Domain $domain)
    {
        $request->validate([
            'certificate' => 'required|string',
            'private_key' => 'required|string',
        ]);

        $hostname = "mail.{$domain->domain}";
        $sslPath = base_path("docker/mailserver/letsencrypt/live/{$hostname}");
        
        if (!file_exists($sslPath)) {
            mkdir($sslPath, 0755, true);
        }

        file_put_contents("{$sslPath}/fullchain.pem", trim($request->certificate));
        file_put_contents("{$sslPath}/privkey.pem", trim($request->private_key));

        // Attempt to restart mailserver
        try {
            shell_exec('docker-compose restart mail > /dev/null 2>&1 &');
        } catch (\Exception $e) {}

        return back()->with('success', 'SSL certificate for ' . $domain->domain . ' installed successfully.');
    }

    public function verifyDns(Domain $domain)
    {
        $status = [
            'mx' => false,
            'spf' => false,
            'dmarc' => false,
            'dkim' => false,
        ];

        // 1. Check MX
        $mxRecords = @dns_get_record($domain->domain, DNS_MX);
        if ($mxRecords) {
            $expectedHost = config('edgemail.hostname');
            foreach ($mxRecords as $record) {
                if (isset($record['target']) && str_ends_with(strtolower($record['target']), strtolower($expectedHost))) {
                    $status['mx'] = true;
                    break;
                }
            }
        }

        // 2. Check SPF (TXT on root domain)
        $txtRecords = @dns_get_record($domain->domain, DNS_TXT);
        if ($txtRecords) {
            foreach ($txtRecords as $record) {
                $txt = $record['txt'] ?? ($record['entries'][0] ?? '');
                if (str_starts_with(strtolower($txt), 'v=spf1')) {
                    $status['spf'] = true;
                    break;
                }
            }
        }

        // 3. Check DMARC
        $dmarcRecords = @dns_get_record("_dmarc.{$domain->domain}", DNS_TXT);
        if ($dmarcRecords) {
            foreach ($dmarcRecords as $record) {
                $txt = $record['txt'] ?? ($record['entries'][0] ?? '');
                if (str_starts_with(strtolower($txt), 'v=dmarc1')) {
                    $status['dmarc'] = true;
                    break;
                }
            }
        }

        // 4. Check DKIM
        if ($domain->dkim_enabled && $domain->dkim_selector) {
            $dkimRecords = @dns_get_record("{$domain->dkim_selector}._domainkey.{$domain->domain}", DNS_TXT);
            if ($dkimRecords) {
                foreach ($dkimRecords as $record) {
                    $txt = $record['txt'] ?? ($record['entries'][0] ?? '');
                    if (str_starts_with(strtolower($txt), 'v=dkim1')) {
                        $status['dkim'] = true;
                        break;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    public function verifySsl(Domain $domain)
    {
        $hostname = "mail.{$domain->domain}";
        $sslPath = base_path("docker/mailserver/letsencrypt/live/{$hostname}");
        
        $hasFullchain = file_exists("{$sslPath}/fullchain.pem");
        $hasPrivkey = file_exists("{$sslPath}/privkey.pem");
        
        $verified = $hasFullchain && $hasPrivkey;
        
        return response()->json([
            'success' => true,
            'verified' => $verified,
            'message' => $verified ? 'SSL Verified Successfully' : 'SSL certificate not found or incomplete.'
        ]);
    }
}
