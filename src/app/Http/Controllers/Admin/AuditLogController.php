<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->event_type, fn ($q, $t) => $q->where('event_type', $t))
            ->when($request->user_id, fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->from, fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->where('created_at', '<=', $d))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/AuditLog/Index', [
            'logs' => $logs,
            'filters' => $request->only(['event_type', 'user_id', 'from', 'to']),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        return Inertia::render('Admin/AuditLog/Show', [
            'log' => $auditLog->load('user'),
        ]);
    }
}
