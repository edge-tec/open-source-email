<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class LogApiController extends Controller
{
    public function index(Request $request) {
        return response()->json(EmailLog::when($request->direction, fn($q) => $q->where('direction', $request->direction))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(25));
    }
    public function show(EmailLog $log) { return response()->json($log); }
    public function summary() {
        return response()->json([
            'total_today' => EmailLog::whereDate('created_at', today())->count(),
            'sent_today' => EmailLog::outbound()->whereDate('created_at', today())->count(),
            'received_today' => EmailLog::inbound()->whereDate('created_at', today())->count(),
            'spam_today' => EmailLog::where('is_spam', true)->whereDate('created_at', today())->count(),
        ]);
    }
}
