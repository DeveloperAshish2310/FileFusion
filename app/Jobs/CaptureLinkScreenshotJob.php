<?php

namespace App\Jobs;

use App\Helpers\Encryptor;
use App\Http\Controllers\WebsiteController;
use App\Models\Links;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CaptureLinkScreenshotJob implements ShouldQueue
{
    use Queueable;

    public int $linkId;
    public bool $force;

    /**
     * Create a new job instance.
     */
    public function __construct(int $linkId, bool $force = false)
    {
        $this->linkId = $linkId;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $link = Links::find($this->linkId);
            if (!$link || $link->is_trashed) {
                return;
            }

            // If thumbnail already exists and not forcing refresh, skip
            if (!empty($link->thumbnail) && !$this->force) {
                return;
            }

            $rawUrl = Encryptor::decrypt($link->url);
            if (empty($rawUrl)) {
                return;
            }

            $webSiteController = new WebsiteController();
            $screenshotResponse = $webSiteController->getWebScreenshot(base64_encode($rawUrl));
            $data = $screenshotResponse->getData();

            if (isset($data->code) && $data->code === 200 && !empty($data->ss_path)) {
                $link->thumbnail = $data->ss_path;
                $link->save();

                Log::info("CaptureLinkScreenshotJob: Screenshot captured successfully for Link #{$link->id}");
            } else {
                Log::warning("CaptureLinkScreenshotJob: Screenshot capture failed for Link #{$link->id}", [
                    'url' => $rawUrl,
                    'message' => $data->message ?? 'Unknown error'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("CaptureLinkScreenshotJob Exception for Link #{$this->linkId}: " . $e->getMessage());
        }
    }
}
