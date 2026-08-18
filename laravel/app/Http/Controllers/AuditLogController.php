<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Super Admin Global Audit Trail Dashboard.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'action' => $request->input('action', 'all'),
            'ip' => $request->input('ip'),
            'user_id' => $request->input('user_id'),
        ];

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['action']) && $filters['action'] !== 'all') {
            $query->where('action', 'like', "{$filters['action']}%");
        }

        if (!empty($filters['ip'])) {
            $query->where('ip_address', $filters['ip']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $logs = $query->paginate(25)->withQueryString();

        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', today())->count(),
            'warnings' => ActivityLog::where('status', 'warning')->count(),
            'dangers' => ActivityLog::where('status', 'danger')->count(),
        ];

        return view('panel.admin.activity-logs', compact('logs', 'filters', 'stats'));
    }

    /**
     * Stream CSV export of audit logs.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'action' => $request->input('action', 'all'),
            'ip' => $request->input('ip'),
        ];

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['action']) && $filters['action'] !== 'all') {
            $query->where('action', 'like', "{$filters['action']}%");
        }

        $filename = 'audit_logs_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Timestamp (UTC)', 'User Email', 'Action', 'Status', 'Description', 'IP Address', 'Device', 'OS', 'Browser', 'HTTP Method', 'URL', 'Context JSON']);

            $query->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? $log->created_at->toDateTimeString() : '',
                        $log->user_email ?: ($log->user ? $log->user->email : 'Guest / System'),
                        $log->action,
                        strtoupper($log->status),
                        $log->description,
                        $log->ip_address,
                        $log->device,
                        $log->os,
                        $log->browser,
                        $log->method,
                        $log->url,
                        $log->context ? json_encode($log->context) : '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * User's personal security activity history.
     */
    public function userActivity(Request $request)
    {
        $user = Auth::user();

        $logs = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'device' => $log->device ?: 'Desktop',
                    'os' => $log->os,
                    'browser' => $log->browser,
                    'status' => $log->status,
                    'date_formatted' => $log->created_at ? $log->created_at->format('d M Y, h:i A') : '',
                    'time_ago' => $log->created_at ? $log->created_at->diffForHumans() : '',
                ];
            });

        return response()->json([
            'ok' => 1,
            'logs' => $logs,
        ]);
    }
}
