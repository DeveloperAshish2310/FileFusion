<?php

namespace App\Console\Commands;

use App\Helpers\Encryptor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EncryptDbCategoriesAndLinksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:encrypt-categories-and-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt all plaintext categories and links records in MySQL using AES-256 encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==========================================================");
        $this->info("   FILE FUSION: CATEGORIES & LINKS ENCRYPTION MIGRATION  ");
        $this->info("==========================================================");

        // 1. Migrate Categories
        $categories = DB::table('categories')->get();
        $this->info("Found " . count($categories) . " records in 'categories' table.");
        $catEncrypted = 0;

        foreach ($categories as $cat) {
            $title = $cat->title ?? '';
            $desc = $cat->description ?? '';

            $isTitleEncrypted = str_contains($title, 'eyJpdiI6') || str_contains($title, 'eyJtYWMi');
            $isDescEncrypted = empty($desc) || str_contains($desc, 'eyJpdiI6') || str_contains($desc, 'eyJtYWMi');

            if (!$isTitleEncrypted || !$isDescEncrypted) {
                DB::table('categories')->where('id', $cat->id)->update([
                    'title' => $isTitleEncrypted ? $title : Encryptor::encrypt($title),
                    'description' => $isDescEncrypted ? $desc : Encryptor::encrypt($desc),
                ]);
                $catEncrypted++;
            }
        }
        $this->info("✓ Categories Migration Complete: {$catEncrypted} records encrypted.");

        // 2. Migrate Links
        $links = DB::table('links')->get();
        $this->info("Found " . count($links) . " records in 'links' table.");
        $linkEncrypted = 0;

        foreach ($links as $link) {
            $title = $link->title ?? '';
            $url = $link->url ?? '';
            $desc = $link->description ?? '';
            $tags = $link->tags ?? '';

            $isTitleEnc = str_contains($title, 'eyJpdiI6') || str_contains($title, 'eyJtYWMi');
            $isUrlEnc = str_contains($url, 'eyJpdiI6') || str_contains($url, 'eyJtYWMi');
            $isDescEnc = empty($desc) || str_contains($desc, 'eyJpdiI6') || str_contains($desc, 'eyJtYWMi');
            $isTagsEnc = empty($tags) || str_contains($tags, 'eyJpdiI6') || str_contains($tags, 'eyJtYWMi');

            if (!$isTitleEnc || !$isUrlEnc || !$isDescEnc || !$isTagsEnc) {
                DB::table('links')->where('id', $link->id)->update([
                    'title' => $isTitleEnc ? $title : Encryptor::encrypt($title),
                    'url' => $isUrlEnc ? $url : Encryptor::encrypt($url),
                    'description' => $isDescEnc ? $desc : Encryptor::encrypt($desc),
                    'tags' => $isTagsEnc ? $tags : Encryptor::encrypt($tags),
                ]);
                $linkEncrypted++;
            }
        }
        $this->info("✓ Links Migration Complete: {$linkEncrypted} records encrypted.");

        $this->info("----------------------------------------------------------");
        $this->info("🎉 ALL CATEGORIES & LINKS DATABASE ENCRYPTION COMPLETED!");
        $this->info("----------------------------------------------------------");

        return 0;
    }
}
