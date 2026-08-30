<?php

/**
 * FileFusion Unified Master Cron Runner
 * 
 * Synchronously executes all scheduled tasks, maintenance routines, and queue workers
 * with strict non-overlapping mutex locks and execution time limits.
 * 
 * Usage in cPanel / Linux Crontab:
 * * * * * cd /home1/ashishkumar/public_html/files.ashishkumar.info && /usr/local/bin/php cron_runner.php >> /home1/ashishkumar/public_html/files.ashishkumar.info/cron.log 2>&1
 * 
 * Usage from CLI / Terminal:
 * php cron_runner.php
 */

define('LARAVEL_START', microtime(true));

// Prevent script from hanging indefinitely (strict 30-second cap)
@set_time_limit(30);
@ini_set('memory_limit', '256M');

$basePath = __DIR__;
$lockDir = $basePath . '/storage/framework';
$lockFile = $lockDir . '/cron_master.lock';

// Ensure storage directory exists
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0755, true);
}

// 1. Non-blocking Lock Check to Prevent Overlapping Executions
$lockHandle = @fopen($lockFile, 'c+');
if (!$lockHandle) {
    echo "[" . date('Y-m-d H:i:s') . "] [ERROR] Unable to open lock file: {$lockFile}\n";
    exit(1);
}

// Attempt to acquire an exclusive, non-blocking lock
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $lockData = @fread($lockHandle, 512);
    $runningInfo = !empty($lockData) ? trim($lockData) : 'active';
    echo "[" . date('Y-m-d H:i:s') . "] [INFO] Previous cron run is still in progress ({$runningInfo}). Exiting cleanly to avoid overlap.\n";
    fclose($lockHandle);
    exit(0);
}

// Record current process PID and timestamp into lock file
ftruncate($lockHandle, 0);
rewind($lockHandle);
fwrite($lockHandle, "PID: " . getmypid() . " | Started: " . date('Y-m-d H:i:s'));
fflush($lockHandle);

// Register shutdown function to release lock on normal or abnormal script termination
register_shutdown_function(function () use ($lockHandle, $lockFile) {
    if (is_resource($lockHandle)) {
        @flock($lockHandle, LOCK_UN);
        @fclose($lockHandle);
    }
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
});

try {
    // 2. Bootstrap Laravel Environment
    require_once $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $startTime = microtime(true);
    echo "[" . date('Y-m-d H:i:s') . "] [START] Starting Unified Master Cron...\n";

    // 3. Task A: Run Laravel Scheduled Tasks
    $scheduleStatus = 'OK';
    try {
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
    } catch (\Throwable $e) {
        $scheduleStatus = 'Error: ' . $e->getMessage();
    }

    // 4. Task B: Execute System Maintenance & Push Alerts via CronController
    $cronController = new \App\Http\Controllers\CronController();
    $cronRequest = new \Illuminate\Http\Request();

    // B1. Todo Deadlines & Reminders
    $todosCount = 0;
    try {
        $todosRes = $cronController->todoDeadlines($cronRequest);
        $todosData = json_decode($todosRes->getContent(), true);
        $todosCount = $todosData['todos_notified'] ?? 0;
    } catch (\Throwable $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [WARN] Todo notification check failed: " . $e->getMessage() . "\n";
    }

    // B2. Vault Session Inactivity Monitor
    $vaultSecured = 0;
    try {
        $vaultRes = $cronController->vaultSecurity($cronRequest);
        $vaultData = json_decode($vaultRes->getContent(), true);
        $vaultSecured = $vaultData['vault_sessions_checked'] ?? 0;
    } catch (\Throwable $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [WARN] Vault security check failed: " . $e->getMessage() . "\n";
    }

    // B3. Stale Upload Chunks Cleanup
    $chunksPurged = 0;
    try {
        $chunksRes = $cronController->cleanupChunks($cronRequest);
        $chunksData = json_decode($chunksRes->getContent(), true);
        $chunksPurged = $chunksData['temp_chunks_purged'] ?? 0;
    } catch (\Throwable $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [WARN] Chunks cleanup failed: " . $e->getMessage() . "\n";
    }

    // 5. Task C: Process Background Queue Jobs (Bounded to max 5 jobs / 15s)
    $queueJobsProcessed = 0;
    try {
        $queueRes = $cronController->queueJobs($cronRequest);
        $queueData = json_decode($queueRes->getContent(), true);
        $queueJobsProcessed = $queueData['jobs_processed'] ?? 0;
    } catch (\Throwable $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [WARN] Queue processing failed: " . $e->getMessage() . "\n";
    }

    $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
    $memoryUsedMb = round(memory_get_peak_usage(true) / (1024 * 1024), 2);

    echo "[" . date('Y-m-d H:i:s') . "] [DONE] Master Cron finished in {$executionTimeMs}ms (Memory: {$memoryUsedMb}MB)\n";
    echo "  - Schedule Run: {$scheduleStatus}\n";
    echo "  - Todo Notifications Dispatched: {$todosCount}\n";
    echo "  - Vault Sessions Checked: {$vaultSecured}\n";
    echo "  - Stale Chunks Purged: {$chunksPurged}\n";
    echo "  - Queue Jobs Processed: {$queueJobsProcessed}\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] [FATAL] Cron encountered an unhandled exception: " . $e->getMessage() . "\n";
} finally {
    if (is_resource($lockHandle)) {
        @flock($lockHandle, LOCK_UN);
        @fclose($lockHandle);
    }
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
}
