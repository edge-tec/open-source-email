<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\StorageUsage;
use App\Models\Domain;

class StorageController extends Controller
{
    public function index() {
        $domains = Domain::withCount('mailboxes')->get()->map(function ($domain) {
            $domain->total_used = $domain->mailboxes()->sum('used_quota');
            $domain->total_quota = $domain->max_quota;
            return $domain;
        });
        $storage = StorageUsage::with('mailbox', 'domain')->orderByDesc('used_bytes')->paginate(20);
        return view('admin.storage.index', compact('domains', 'storage'));
    }
}
