<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SpamFilter;
use App\Models\Domain;
use Illuminate\Http\Request;

class SpamController extends Controller
{
    public function index() {
        $filters = SpamFilter::with('domain', 'mailbox')->latest()->paginate(20);
        $domains = Domain::active()->get();
        return view('admin.spam.index', compact('filters', 'domains'));
    }

    public function storeFilter(Request $request) {
        $request->validate(['value' => 'required', 'filter_type' => 'required|in:whitelist,blacklist,custom_rule', 'action' => 'required']);
        SpamFilter::create($request->only(['mailbox_id', 'domain_id', 'filter_type', 'value', 'action', 'priority', 'status']));
        return back()->with('success', 'Filter created.');
    }

    public function destroyFilter(SpamFilter $filter) {
        $filter->delete();
        return back()->with('success', 'Filter removed.');
    }
}
