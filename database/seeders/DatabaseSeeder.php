<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LandingPageSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Super Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@filefusion.io'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'vault_pass' => Hash::make('1234'),
                'enc_key' => Str::random(32),
                'role' => 'super_admin',
                'account_type' => '1',
                'status' => 1,
                'storage_quota' => 0, // 0 = Unlimited
                'api_access_enabled' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Default Standard User Account
        $user = User::firstOrCreate(
            ['email' => 'user@filefusion.io'],
            [
                'name' => 'Demo User',
                'username' => 'demouser',
                'password' => Hash::make('password123'),
                'vault_pass' => Hash::make('1234'),
                'enc_key' => Str::random(32),
                'role' => 'user',
                'account_type' => '0',
                'status' => 1,
                'storage_quota' => 5368709120, // 5 GB default quota
                'api_access_enabled' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Create Default Categories for the Admin and Demo User
        $defaultCategories = [
            ['title' => 'Documents', 'type' => 'files', 'description' => 'Important contracts, reports, and PDFs.'],
            ['title' => 'Work Projects', 'type' => 'files', 'description' => 'Project assets, sprint files, and client briefs.'],
            ['title' => 'Dev & Cloud', 'type' => 'links', 'description' => 'Developer documentation, GitHub repos, and API references.'],
            ['title' => 'Finance & Vault', 'type' => 'passwords', 'description' => 'Encrypted credentials, financial portals, and license keys.'],
            ['title' => 'Sprint Tasks', 'type' => 'tasks', 'description' => 'Sprint milestones, action items, and checklists.'],
        ];

        foreach ([$admin, $user] as $account) {
            foreach ($defaultCategories as $cat) {
                $exists = Category::where('user_id', $account->id)
                    ->where('type', $cat['type'])
                    ->get()
                    ->contains(function ($c) use ($cat) {
                        return $c->title === $cat['title'];
                    });

                if (!$exists) {
                    Category::create([
                        'user_id' => $account->id,
                        'type' => $cat['type'],
                        'title' => $cat['title'],
                        'description' => $cat['description'],
                        'categories' => [$cat['title']],
                        'is_new' => false,
                        'is_hidden' => false,
                    ]);
                }
            }
        }

        // 4. Seed Default CMS Landing Page & Email Template Settings
        LandingPageSetting::seedDefaults();

        $this->command->info('✅ FileFusion database seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Username', 'Password', 'Vault PIN', 'Storage Quota'],
            [
                ['Super Admin', 'admin@filefusion.io', 'admin', 'password123', '1234', 'Unlimited'],
                ['Standard User', 'user@filefusion.io', 'demouser', 'password123', '1234', '5 GB'],
            ]
        );
    }
}
