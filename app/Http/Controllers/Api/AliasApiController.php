<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Alias;
use Illuminate\Http\Request;

class AliasApiController extends Controller
{
    public function index(Request $request) { return response()->json(Alias::with('domain')->paginate(15)); }
    public function store(Request $request) {
        $request->validate(['source' => 'required', 'destination' => 'required', 'domain_id' => 'required|exists:domains,id']);
        return response()->json(Alias::create($request->only(['domain_id', 'source', 'destination', 'status', 'is_catchall', 'comment'])), 201);
    }
    public function show(Alias $alias) { return response()->json($alias); }
    public function update(Request $request, Alias $alias) {
        $alias->update($request->only(['source', 'destination', 'status', 'is_catchall', 'comment']));
        return response()->json($alias);
    }
    public function destroy(Alias $alias) { $alias->delete(); return response()->json(null, 204); }
}
