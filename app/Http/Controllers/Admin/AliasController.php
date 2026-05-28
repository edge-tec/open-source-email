<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alias;
use App\Models\Domain;
use App\Services\MailServerSyncService;
use Illuminate\Http\Request;

class AliasController extends Controller
{
    public function index(Request $request)
    {
        $aliases = Alias::with('domain')
            ->when($request->search, fn($q) => $q->where('source', 'like', "%{$request->search}%"))
            ->latest()->paginate(15);
        return view('admin.aliases.index', compact('aliases'));
    }

    public function create()
    {
        $domains = Domain::active()->get();
        return view('admin.aliases.create', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source' => 'required|string|max:255',
            'destination' => 'required|string',
            'domain_id' => 'required|exists:domains,id',
        ]);

        Alias::create($request->only(['domain_id', 'source', 'destination', 'status', 'is_catchall', 'comment']));
        app(MailServerSyncService::class)->syncAliases();
        return redirect()->route('admin.aliases.index')->with('success', 'Alias created.');
    }

    public function edit(Alias $alias)
    {
        $domains = Domain::active()->get();
        return view('admin.aliases.edit', compact('alias', 'domains'));
    }

    public function update(Request $request, Alias $alias)
    {
        $alias->update($request->only(['source', 'destination', 'status', 'is_catchall', 'comment']));
        app(MailServerSyncService::class)->syncAliases();
        return redirect()->route('admin.aliases.index')->with('success', 'Alias updated.');
    }

    public function destroy(Alias $alias)
    {
        $alias->delete();
        app(MailServerSyncService::class)->syncAliases();
        return redirect()->route('admin.aliases.index')->with('success', 'Alias deleted.');
    }
}
