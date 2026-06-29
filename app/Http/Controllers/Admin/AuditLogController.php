<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');
        if ($request->filled('event'))   $query->where('event', $request->event);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $logs   = $query->paginate(50)->withQueryString();
        $events = AuditLog::distinct()->pluck('event');
        return view('admin.audit-logs.index', compact('logs', 'events'));
    }
}
