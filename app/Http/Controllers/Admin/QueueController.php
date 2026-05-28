<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\QueueLog;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index() {
        $queue = QueueLog::latest()->paginate(25);
        $stats = [
            'queued' => QueueLog::where('status', 'queued')->count(),
            'active' => QueueLog::where('status', 'active')->count(),
            'deferred' => QueueLog::where('status', 'deferred')->count(),
            'bounced' => QueueLog::where('status', 'bounced')->count(),
        ];
        return view('admin.queue.index', compact('queue', 'stats'));
    }

    public function retry($id) {
        $log = QueueLog::findOrFail($id);
        $log->update(['status' => 'queued', 'attempts' => 0, 'error_message' => null]);
        return back()->with('success', 'Message re-queued.');
    }

    public function destroy($id) {
        QueueLog::findOrFail($id)->delete();
        return back()->with('success', 'Queue entry removed.');
    }
}
