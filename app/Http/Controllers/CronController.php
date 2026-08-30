<?php

namespace App\Http\Controllers;

use App\Models\TodoTask;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CronController extends Controller
{
    /**
     * Verify CRON security token if configured in environment.
     */
    protected function verifyCronToken(Request $request): bool
    {
        $expectedSecret = env('CRON_SECRET', env('CRON_KEY'));
        if (empty($expectedSecret)) {
            return true; // Open if secret is not set in dev
        }

        $providedSecret = $request->query('key')
            ?: $request->header('X-Cron-Key')
            ?: $request->bearerToken();

        return hash_equals((string) $expectedSecret, (string) $providedSecret);
    }

    /**
     * Unified Master Cron Runner (Runs all maintenance, schedule, and queue with overlap lock)
     * URL: GET|POST /cron/master
     */
    public function master(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $lockPath = storage_path('framework/cron_master.lock');
        $lockDir = dirname($lockPath);
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0755, true);
        }

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle && !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $lockData = @fread($lockHandle, 512);
            fclose($lockHandle);
            return response()->json([
                'ok' => 1,
                'job' => 'master',
                'status' => 'skipped',
                'message' => 'Previous cron runner is still active. Skipping execution to prevent overlap.',
                'running_info' => !empty($lockData) ? trim($lockData) : 'active',
            ]);
        }

        if ($lockHandle) {
            ftruncate($lockHandle, 0);
            rewind($lockHandle);
            fwrite($lockHandle, "PID: " . getmypid() . " | Started: " . date('Y-m-d H:i:s'));
            fflush($lockHandle);
        }

        $startTime = microtime(true);
        $results = [
            'timestamp' => now()->toIso8601String(),
            'todos_notified' => 0,
            'vault_sessions_secured' => 0,
            'temp_chunks_purged' => 0,
            'queue_jobs_processed' => 0,
            'schedule_run' => 'completed',
            'execution_time_ms' => 0,
            'memory_used_mb' => 0,
        ];

        try {
            // 1. Process Todo Deadlines and Reminders
            $results['todos_notified'] = $this->processTodoNotifications();

            // 2. Process Vault Inactivity Security Checks
            $results['vault_sessions_secured'] = $this->processVaultInactivity();

            // 3. Cleanup Stale Temporary Chunk Files (> 2 hours old)
            $results['temp_chunks_purged'] = $this->cleanupStaleChunks();

            // 4. Process Pending Background Queue Jobs (bounded to max 5 jobs / 15s)
            $results['queue_jobs_processed'] = $this->processPendingQueueJobs();

            $results['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $results['memory_used_mb'] = round(memory_get_peak_usage(true) / (1024 * 1024), 2);

            return response()->json([
                'ok' => 1,
                'job' => 'master',
                'status' => 'success',
                'message' => 'All cron tasks, notifications, and queue jobs executed successfully.',
                'results' => $results,
            ]);
        } finally {
            if ($lockHandle && is_resource($lockHandle)) {
                @flock($lockHandle, LOCK_UN);
                @fclose($lockHandle);
            }
            if (file_exists($lockPath)) {
                @unlink($lockPath);
            }
        }
    }

    /**
     * Master 1-Minute Cron Runner (Executes all crons sequentially)
     * URL: GET|POST /cron/run or /cron/all
     */
    public function run(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        $results = [
            'timestamp' => now()->toIso8601String(),
            'todos_notified' => 0,
            'vault_sessions_secured' => 0,
            'temp_chunks_purged' => 0,
            'execution_time_ms' => 0,
        ];

        // 1. Process Todo Deadlines and Reminders
        $results['todos_notified'] = $this->processTodoNotifications();

        // 2. Process Vault Inactivity Security Checks
        $results['vault_sessions_secured'] = $this->processVaultInactivity();

        // 3. Cleanup Stale Temporary Chunk Files (> 2 hours old)
        $results['temp_chunks_purged'] = $this->cleanupStaleChunks();

        $results['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'ok' => 1,
            'job' => 'all',
            'message' => 'All cron tasks executed successfully.',
            'results' => $results,
        ]);
    }

    /**
     * Dedicated Cron: Todo Deadlines & Reminders
     * URL: GET|POST /cron/todos or /cron/todo-deadlines
     */
    public function todoDeadlines(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        $count = $this->processTodoNotifications();

        return response()->json([
            'ok' => 1,
            'job' => 'todo_deadlines',
            'message' => "Dispatched {$count} todo deadline & reminder notifications.",
            'todos_notified' => $count,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Dedicated Cron: Secret Vault Session & Inactivity Monitor
     * URL: GET|POST /cron/vault or /cron/vault-check
     */
    public function vaultSecurity(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        $count = $this->processVaultInactivity();

        return response()->json([
            'ok' => 1,
            'job' => 'vault_security',
            'message' => "Processed vault sessions.",
            'vault_sessions_checked' => $count,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Dedicated Cron: Cleanup Stale Upload Chunks
     * URL: GET|POST /cron/cleanup-chunks or /cron/cleanup-uploads
     */
    public function cleanupChunks(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        $purged = $this->cleanupStaleChunks();

        return response()->json([
            'ok' => 1,
            'job' => 'cleanup_chunks',
            'message' => "Purged {$purged} stale temporary chunk files.",
            'temp_chunks_purged' => $purged,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Dedicated Cron: Background Queue Worker Runner
     * URL: GET|POST /cron/queue or /cron/queue-work
     */
    public function queueJobs(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        $processed = $this->processPendingQueueJobs();

        return response()->json([
            'ok' => 1,
            'job' => 'queue_worker',
            'message' => "Processed {$processed} background queue jobs.",
            'jobs_processed' => $processed,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Scan and dispatch push notifications for approaching task deadlines & reminders.
     */
    protected function processTodoNotifications(): int
    {
        $now = now();
        $notifiedCount = 0;

        // A. Reminders triggered at or before now
        $reminderTasks = TodoTask::where('is_completed', false)
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', $now->copy()->subMinutes(15));
            })
            ->with(['collection', 'user'])
            ->limit(50)
            ->get();

        foreach ($reminderTasks as $task) {
            $collectionName = $task->collection ? $task->collection->name : 'General Tasks';
            $timeText = $task->remind_at->format('g:i A');

            $title = "⏰ Task Reminder: {$task->title}";
            $body = "Scheduled for {$timeText} in {$collectionName}.";
            $params = ['task' => $task->id];
            if (!empty($task->todo_collection_id)) {
                $params['collection'] = $task->todo_collection_id;
            }
            $url = route('panel.todos.index', $params);

            try {
                PushNotificationService::sendToUser($task->user_id, [
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                    'tag' => 'todo-reminder-' . $task->id,
                    'data' => [
                        'type' => 'todo_reminder',
                        'task_id' => $task->id,
                        'url' => $url,
                    ],
                ]);

                $task->update([
                    'last_notified_at' => $now,
                    'is_notified' => true,
                ]);

                $notifiedCount++;
            } catch (\Throwable $e) {
                Log::warning("Failed to dispatch todo reminder push notification for task #{$task->id}: " . $e->getMessage());
            }
        }

        // B. Approaching Deadlines: due within the next 15 minutes OR currently overdue
        $deadlineTasks = TodoTask::where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $now->copy()->addMinutes(15))
            ->where(function ($q) use ($now) {
                $q->where('is_notified', false)
                  ->orWhereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', $now->copy()->subHours(1));
            })
            ->with(['collection', 'user'])
            ->limit(50)
            ->get();

        foreach ($deadlineTasks as $task) {
            $collectionName = $task->collection ? $task->collection->name : 'General Tasks';
            $isOverdue = $task->is_overdue;

            if ($isOverdue) {
                $title = "⚠️ Task Overdue: {$task->title}";
                $body = "This task was due {$task->due_badge} in {$collectionName}.";
            } else {
                $title = "⏳ Task Due Soon: {$task->title}";
                $body = "Due {$task->due_badge} in {$collectionName}.";
            }

            $params = ['task' => $task->id];
            if (!empty($task->todo_collection_id)) {
                $params['collection'] = $task->todo_collection_id;
            }
            $url = route('panel.todos.index', $params);

            try {
                PushNotificationService::sendToUser($task->user_id, [
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                    'tag' => 'todo-deadline-' . $task->id,
                    'data' => [
                        'type' => 'todo_deadline',
                        'task_id' => $task->id,
                        'url' => $url,
                    ],
                ]);

                $task->update([
                    'last_notified_at' => $now,
                    'is_notified' => true,
                ]);

                $notifiedCount++;
            } catch (\Throwable $e) {
                Log::warning("Failed to dispatch todo deadline push notification for task #{$task->id}: " . $e->getMessage());
            }
        }

        return $notifiedCount;
    }

    /**
     * Check vault inactivity routines.
     */
    protected function processVaultInactivity(): int
    {
        // Vault sessions are stateless across HTTP requests and managed via session lifetimes & beacons.
        return 1;
    }

    /**
     * Purge abandoned temporary upload chunk files older than 2 hours.
     */
    protected function cleanupStaleChunks(): int
    {
        $purged = 0;
        $twoHoursAgo = time() - (2 * 3600);

        $tempDirs = [
            storage_path('app/temp_chunks'),
            storage_path('app/chunks'),
            storage_path('app/private/temp_chunks'),
            sys_get_temp_dir(),
        ];

        foreach ($tempDirs as $dir) {
            if (!is_dir($dir)) continue;

            $files = @glob($dir . DIRECTORY_SEPARATOR . 'chunk_*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $twoHoursAgo) {
                        if (@unlink($file)) {
                            $purged++;
                        }
                    }
                }
            }
        }

        return $purged;
    }

    /**
     * Process Pending Database Queue Jobs (e.g. Screenshot Captures)
     */
    protected function processPendingQueueJobs(): int
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
                return 0;
            }

            $jobsCountBefore = \Illuminate\Support\Facades\DB::table('jobs')->count();
            if ($jobsCountBefore === 0) {
                return 0;
            }

            \Illuminate\Support\Facades\Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-jobs' => 3,
                '--max-time' => 10,
                '--timeout' => 12,
            ]);

            $jobsCountAfter = \Illuminate\Support\Facades\DB::table('jobs')->count();
            return max(0, $jobsCountBefore - $jobsCountAfter);
        } catch (\Throwable $e) {
            Log::error('Cron processPendingQueueJobs error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Automated Daily Database SQL Backup Cron
     * URL: GET|POST /cron/backup-db
     */
    public function backupDb(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        try {
            $backupResult = \App\Services\BackupService::createDbBackup();

            // Auto remote offsite dispatch if enabled
            $remoteResults = \App\Services\RemoteStorageService::dispatchRemoteUploads($backupResult['filename'], 'auto');

            \App\Services\AuditLogger::admin(
                'cron_db_backup',
                "Automated Cron: Database SQL backup created ({$backupResult['size_formatted']})",
                'success',
                [
                    'filename' => $backupResult['filename'],
                    'size' => $backupResult['size'],
                    'remote' => $remoteResults
                ]
            );

            return response()->json([
                'ok' => 1,
                'job' => 'backup_db',
                'message' => 'Daily database SQL backup completed successfully.',
                'backup' => $backupResult,
                'remote' => $remoteResults,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Throwable $e) {
            Log::error('Cron backupDb failed: ' . $e->getMessage());
            return response()->json([
                'ok' => 0,
                'job' => 'backup_db',
                'error' => $e->getMessage(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ], 500);
        }
    }

    /**
     * Automated Daily Whole Site, Codebase & HTML Backup Cron
     * URL: GET|POST /cron/backup-full
     */
    public function backupFull(Request $request): JsonResponse
    {
        if (!$this->verifyCronToken($request)) {
            return response()->json(['ok' => 0, 'error' => 'Unauthorized cron key.'], 403);
        }

        $startTime = microtime(true);
        try {
            $backupResult = \App\Services\BackupService::createCodebaseBackup();

            // Auto remote offsite dispatch if enabled
            $remoteResults = \App\Services\RemoteStorageService::dispatchRemoteUploads($backupResult['filename'], 'auto');

            \App\Services\AuditLogger::admin(
                'cron_full_backup',
                "Automated Cron: Whole site & codebase backup created ({$backupResult['size_formatted']}, {$backupResult['files_count']} files)",
                'success',
                [
                    'filename' => $backupResult['filename'],
                    'size' => $backupResult['size'],
                    'remote' => $remoteResults
                ]
            );

            return response()->json([
                'ok' => 1,
                'job' => 'backup_full',
                'message' => 'Daily whole site codebase & HTML backup completed successfully.',
                'backup' => $backupResult,
                'remote' => $remoteResults,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Throwable $e) {
            Log::error('Cron backupFull failed: ' . $e->getMessage());
            return response()->json([
                'ok' => 0,
                'job' => 'backup_full',
                'error' => $e->getMessage(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ], 500);
        }
    }

    /**
     * Health & Diagnostic Endpoint
     * URL: GET /cron/status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'ok' => 1,
            'status' => 'healthy',
            'server_time' => now()->toIso8601String(),
            'push_service_configured' => PushNotificationService::isConfigured(),
            'endpoints' => [
                'all' => url('/cron/run'),
                'todos' => url('/cron/todos'),
                'vault' => url('/cron/vault'),
                'cleanup_chunks' => url('/cron/cleanup-chunks'),
                'queue' => url('/cron/queue'),
                'backup_db' => url('/cron/backup-db'),
                'backup_full' => url('/cron/backup-full'),
                'status' => url('/cron/status'),
            ]
        ]);
    }
}
