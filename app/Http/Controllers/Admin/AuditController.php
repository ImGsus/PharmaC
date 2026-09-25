<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $logs = AuditLog::with('user');
            // Keep newest-first as the default until the user sorts a column,
            // otherwise the base latest() would neutralize the requested sort.
            if (! $request->filled('order')) {
                $logs->latest();
            }

            return DataTables::of($logs)
                ->editColumn('created_at', function ($log) {
                    return optional($log->created_at)->format('d M Y H:i');
                })
                ->editColumn('route', function ($log) {
                    return $log->route ?: '-';
                })
                ->editColumn('ip_address', function ($log) {
                    return $log->ip_address ?: '-';
                })
                ->addColumn('user', function ($log) {
                    return optional($log->user)->name ?: 'System';
                })
                ->make(true);
        }

        return view('admin.audit.index', ['title' => 'audit trail']);
    }
}