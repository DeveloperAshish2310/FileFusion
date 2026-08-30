<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cron:run', function () {
    $cron = new \App\Http\Controllers\CronController();
    $req = new \Illuminate\Http\Request();
    $res = $cron->run($req);
    $this->info($res->getContent());
})->purpose('Run background notifications and maintenance cron');

Artisan::command('cron:master', function () {
    $cron = new \App\Http\Controllers\CronController();
    $req = new \Illuminate\Http\Request();
    $res = $cron->master($req);
    $this->info($res->getContent());
})->purpose('Run all cron tasks, schedule, and queue with overlap lock protection');

Artisan::command('cron:queue', function () {
    $cron = new \App\Http\Controllers\CronController();
    $req = new \Illuminate\Http\Request();
    $res = $cron->queueJobs($req);
    $this->info($res->getContent());
})->purpose('Process background async queue jobs');

Artisan::command('cron:backup:db', function () {
    $cron = new \App\Http\Controllers\CronController();
    $req = new \Illuminate\Http\Request();
    $res = $cron->backupDb($req);
    $this->info($res->getContent());
})->purpose('Run daily automated database SQL backup and offsite dispatch');

Artisan::command('cron:backup:full', function () {
    $cron = new \App\Http\Controllers\CronController();
    $req = new \Illuminate\Http\Request();
    $res = $cron->backupFull($req);
    $this->info($res->getContent());
})->purpose('Run daily automated whole site codebase & HTML backup and offsite dispatch');

Artisan::command('backup:db', function () {
    Artisan::call('cron:backup:db');
    $this->info(Artisan::output());
})->purpose('Alias for cron:backup:db');

Artisan::command('backup:full', function () {
    Artisan::call('cron:backup:full');
    $this->info(Artisan::output());
})->purpose('Alias for cron:backup:full');


// 1. Fast, lightweight cron maintenance (Todo push alerts, vault session check, chunks cleanup)
Schedule::command('cron:run')
    ->everyMinute()
    ->runInBackground();

// 2. Background queue worker: processes screenshot captures and async jobs safely, stops when empty
Schedule::command('cron:queue')
    ->everyMinute()
    ->runInBackground();

// 3. Automated Daily Database SQL Backup (runs at 01:00 AM daily)
Schedule::command('cron:backup:db')
    ->dailyAt('01:00')
    ->runInBackground();

// 4. Automated Daily Whole Site & HTML Codebase Backup (runs at 03:30 AM daily)
Schedule::command('cron:backup:full')
    ->dailyAt('03:30')
    ->runInBackground();
