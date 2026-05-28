<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = EmailLog::with('domain')
            ->when($request->direction, fn($q) => $q->where('direction', $request->direction))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($sq) use ($request) {
                $sq->where('sender', 'like', "%{$request->search}%")
                    ->orWhere('recipient', 'like', "%{$request->search}%")
                    ->orWhere('subject', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(25);

        return view('admin.logs.index', compact('logs'));
    }

    public function show(EmailLog $log)
    {
        return view('admin.logs.show', compact('log'));
    }
}
