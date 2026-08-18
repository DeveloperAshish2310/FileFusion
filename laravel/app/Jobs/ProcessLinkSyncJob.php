<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\Links;

class ProcessLinkSyncJob implements ShouldQueue
{
    // use Queueable;
    protected $item, $userId;

    public function __construct($item, $userId)
    {
        $this->item = $item;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info('Processing Link', [
            'url' => $this->item['url'] ?? null,
            'title' => $this->item['title'] ?? null,
        ]);

        try {
            $controller = app(\App\Http\Controllers\WebsiteController::class);
            $screenshotResponse = $controller->getWebScreenshot(base64_encode($this->item['url']));
            $thumbnail = $screenshotResponse->getData()->ss_path ?? null;

            $link = new Links();
            $link->user_id = $this->userId;
            $link->title = $this->item['title'] ?? '';
            $link->url = $this->item['url'] ?? '';
            $link->description = $this->item['description'] ?? '';
            $link->tags = $this->item['tags'] ?? '';
            $link->thumbnail = $thumbnail;
            $link->save();

            Log::info('Link Saved Successfully', [
                'url' => $this->item['url'],
                'id' => $link->id ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to Process Link', [
                'url' => $this->item['url'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
