<?php

namespace App\Http\Controllers;

use App\Helpers\FileEncryptor;
use App\Models\FileModal;
use App\Models\FileShare;
use App\Models\Links;
use App\Models\LandingPageSetting;
use App\Models\Password;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller

{
    /**
     * Helper to resolve encrypted or plain ID.
     */
    private function resolveId($id)
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            return (int) decrypt($id);
        } catch (Exception $e) {
            try {
                return (int) Crypt::decrypt($id);
            } catch (Exception $ex) {
                return (int) $id;
            }
        }
    }

    /**
     * Super Admin Dashboard: Global System & Storage Metrics.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 1)->count();
        $suspendedUsers = User::where('status', 0)->count();

        $totalFiles = FileModal::count();
        $totalTrashedFiles = FileModal::where('is_trashed', 1)->count();

        $totalStorageUsedBytes = User::sum('storage_used');
        $totalStorageQuotaBytes = User::sum('storage_quota');

        // File category breakdown
        $imageCount = FileModal::where('type', 'like', 'image/%')->count();
        $videoCount = FileModal::where('type', 'like', 'video/%')->count();
        $audioCount = FileModal::where('type', 'like', 'audio/%')->count();
        $documentCount = FileModal::where(function ($q) {
            $q->where('type', 'application/pdf')
                ->orWhere('type', 'like', '%word%')
                ->orWhere('type', 'like', '%excel%')
                ->orWhere('type', 'like', '%presentation%')
                ->orWhere('type', 'text/plain');
        })->count();
        $otherCount = max(0, $totalFiles - ($imageCount + $videoCount + $audioCount + $documentCount));

        $recentUsers = User::latest()->limit(8)->get();

        $stats = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'total_files' => $totalFiles,
            'total_trashed_files' => $totalTrashedFiles,
            'storage_used_formatted' => $this->formatBytes($totalStorageUsedBytes),
            'storage_quota_formatted' => $this->formatBytes($totalStorageQuotaBytes),
            'storage_percentage' => $totalStorageQuotaBytes > 0 ? round(($totalStorageUsedBytes / $totalStorageQuotaBytes) * 100, 2) : 0,
            'images_count' => $imageCount,
            'videos_count' => $videoCount,
            'audio_count' => $audioCount,
            'documents_count' => $documentCount,
            'others_count' => $otherCount,
            'recent_users' => $recentUsers,
            'server_php' => PHP_VERSION,
            'server_os' => PHP_OS,
        ];

        return view('panel.admin.dashboard', compact('stats'));
    }

    /**
     * User Management Console: Searchable & Filterable User Directory.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search filter
        $search = $request->get('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Status filter
        $status = $request->get('status');
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        // Role filter
        $role = $request->get('role');
        if (!empty($role)) {
            if ($role === 'super_admin') {
                $query->where(function ($q) {
                    $q->where('account_type', '1')->orWhere('role', 'super_admin');
                });
            } else {
                $query->where('role', $role);
            }
        }

        $users = $query->withCount('files')->latest()->paginate(15)->appends($request->all());

        $roleQuotas = [];
        foreach (User::getAvailableRoles() as $rKey => $rLabel) {
            $roleQuotas[$rKey] = [
                'label' => $rLabel,
                'quota_gb' => User::getDefaultQuotaGbForRole($rKey),
            ];
        }

        if ($request->ajax()) {
            return view('panel.admin.partials.user_table_rows', compact('users', 'search', 'roleQuotas'));
        }

        return view('panel.admin.users', compact('users', 'search', 'status', 'role', 'roleQuotas'));
    }

    /**
     * Update User Role.
     */
    public function updateRole(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        $request->validate([
            'role' => 'required|in:user,pro_user,manager,admin,super_admin',
            'sync_quota' => 'nullable',
        ]);

        // Guard: Prevent demoting self if only remaining super admin
        if ($user->id === Auth::id() && $request->role !== User::ROLE_SUPER_ADMIN) {
            $otherSuperAdmins = User::where('id', '!=', $user->id)
                ->where(function ($q) {
                    $q->where('account_type', '1')->orWhere('role', 'super_admin');
                })->exists();

            if (!$otherSuperAdmins) {
                if ($request->ajax()) {
                    return response()->json(['ok' => 0, 'info' => 'Cannot demote the only remaining Super Admin.'], 400);
                }
                return redirect()->back()->with('error', 'Cannot demote the only remaining Super Admin.');
            }
        }

        $user->setRole($request->role);

        // Optionally apply role default quota
        if ($request->boolean('sync_quota') || $request->input('sync_quota') == '1') {
            $defaultGb = User::getDefaultQuotaGbForRole($request->role);
            $user->setStorageQuotaGB($defaultGb);
        }

        Log::info("User role updated by Super Admin", [
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
            'new_role' => $user->role,
            'new_quota' => $user->getStorageQuotaFormatted()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'info' => "Role for {$user->name} updated to {$user->getRoleDisplayName()} ({$user->getStorageQuotaFormatted()} quota).",
                'role' => $user->role,
                'role_display' => $user->getRoleDisplayName(),
                'quota_formatted' => $user->getStorageQuotaFormatted()
            ]);
        }

        return redirect()->back()->with('success', "Role for {$user->name} updated to {$user->getRoleDisplayName()} ({$user->getStorageQuotaFormatted()} quota).");
    }

    /**
     * Set / Reset User Password.
     */
    public function resetPassword(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        Log::info("User password reset by Super Admin", [
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => "Password for {$user->name} successfully updated."]);
        }

        return redirect()->back()->with('success', "Password for {$user->name} successfully updated.");
    }

    /**
     * Create New User Account.
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|alpha_dash|unique:users,username|max:50',
            'password' => 'required|string|min:6',
            'quota_gb' => 'required|numeric|min:0.5|max:10000',
            'role' => 'required|in:user,pro_user,manager,admin,super_admin',
        ]);

        $quotaBytes = (int) round($request->quota_gb * 1024 * 1024 * 1024);
        $accountType = ($request->role === 'super_admin') ? '1' : '2';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'account_type' => $accountType,
            'storage_quota' => $quotaBytes,
            'storage_used' => 0,
            'status' => 1,
            'email_verified_at' => now(),
            'directory' => $request->username . '-' . Str::uuid()->toString(),
        ]);

        Log::info("Super Admin created new user", [
            'admin_id' => Auth::id(),
            'created_user_id' => $user->id,
            'role' => $user->role,
            'quota' => $request->quota_gb . ' GB'
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => "User {$user->name} successfully created.", 'user' => $user]);
        }

        return redirect()->back()->with('success', "User {$user->name} successfully created.");
    }

    /**
     * Update User Profile & Role.
     */
    public function updateUser(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'username' => "required|string|alpha_dash|max:50|unique:users,username,{$user->id}",
            'role' => 'required|in:user,super_admin',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->account_type = ($request->role === 'super_admin') ? '1' : '2';

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => "User {$user->name} updated successfully."]);
        }

        return redirect()->back()->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Adjust User Storage Quota.
     */
    public function updateQuota(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        $request->validate([
            'quota_gb' => 'required|numeric|min:0.1|max:100000',
        ]);

        $user->setStorageQuotaGB($request->quota_gb);

        Log::info("Storage quota updated by Super Admin", [
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
            'new_quota' => $request->quota_gb . ' GB'
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'info' => "Storage quota for {$user->name} updated to {$request->quota_gb} GB.",
                'new_quota_formatted' => $user->getStorageQuotaFormatted()
            ]);
        }

        return redirect()->back()->with('success', "Storage quota updated to {$request->quota_gb} GB.");
    }

    /**
     * Toggle User Account Status (Activate / Suspend).
     */
    public function toggleStatus(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        // Security barrier: Super admin cannot suspend their own active account
        if ($user->id === Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'info' => 'You cannot suspend your own Super Admin account.'], 400);
            }
            return redirect()->back()->with('error', 'You cannot suspend your own Super Admin account.');
        }

        $newStatus = ($user->status === 1) ? 0 : 1;
        $user->status = $newStatus;
        $user->save();

        $actionWord = ($newStatus === 1) ? 'activated' : 'suspended';

        Log::warning("User account status toggled by Super Admin", [
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
            'new_status' => $actionWord
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'status' => $newStatus,
                'info' => "User account {$user->name} has been {$actionWord}."
            ]);
        }

        return redirect()->back()->with('success', "User account {$user->name} has been {$actionWord}.");
    }

    /**
     * Permanently Delete User and Clean Up Storage Files.
     */
    public function deleteUser(Request $request, $id)
    {
        $userId = $this->resolveId($id);
        $user = User::findOrFail($userId);

        // Security barrier: Prevent deleting self
        if ($user->id === Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'info' => 'You cannot delete your own Super Admin account.'], 400);
            }
            return redirect()->back()->with('error', 'You cannot delete your own Super Admin account.');
        }

        $disk = 'local';
        $userName = $user->name;

        // 1. Crypto-shred all physical user files on disk
        $files = FileModal::where('user_id', $user->id)->withTrashed()->get();
        foreach ($files as $file) {
            if (Storage::disk($disk)->exists($file->path)) {
                FileEncryptor::cryptoShred(Storage::disk($disk)->path($file->path));
            }
            $file->forceDelete();
        }

        // 2. Delete user's uploads directory if present
        if (!empty($user->directory)) {
            $userDirPath = 'private/uploads/files/' . $user->directory;
            if (Storage::disk($disk)->exists($userDirPath)) {
                Storage::disk($disk)->deleteDirectory($userDirPath);
            }
        }

        // 3. Purge associated vault items, links, shares
        Password::where('user_id', $user->id)->withTrashed()->forceDelete();
        Links::where('user_id', $user->id)->withTrashed()->forceDelete();
        FileShare::where('user_id', $user->id)->orWhere('recipient_user_id', $user->id)->delete();

        // 4. Delete user record
        $user->delete();

        Log::warning("User permanently deleted by Super Admin", [
            'admin_id' => Auth::id(),
            'deleted_user' => $userName,
            'deleted_user_id' => $userId
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => "User {$userName} and all associated data permanently deleted."]);
        }

        return redirect()->back()->with('success', "User {$userName} permanently deleted.");
    }

    /**
     * Start Impersonation: Log in as the selected user.
     */
    public function impersonateUser($id)
    {
        $userId = $this->resolveId($id);
        $targetUser = User::findOrFail($userId);

        if ($targetUser->id === Auth::id()) {
            return redirect()->back()->with('error', 'You are already logged into this account.');
        }

        // Save original admin ID in session
        session(['impersonator_admin_id' => Auth::id()]);
        session(['impersonator_admin_name' => Auth::user()->name]);

        Auth::login($targetUser);

        return redirect()->route('panel.dashboard')->with('success', "You are now impersonating {$targetUser->name}.");
    }

    /**
     * Stop Impersonation: Return to Super Admin account.
     */
    public function stopImpersonation()
    {
        if (!session()->has('impersonator_admin_id')) {
            return redirect()->route('panel.dashboard');
        }

        $adminId = session('impersonator_admin_id');
        $adminUser = User::findOrFail($adminId);

        session()->forget('impersonator_admin_id');
        session()->forget('impersonator_admin_name');

        Auth::login($adminUser);

        return redirect()->route('panel.admin.users')->with('success', 'Returned to Super Admin Console.');
    }

    /**
     * Helper to format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Landing Page CMS Editor View.
     */
    public function landingPageEditor()
    {
        LandingPageSetting::seedDefaults();

        $defaults = LandingPageSetting::defaults();
        $settings = [];
        foreach ($defaults as $key => $defaultVal) {
            $settings[$key] = LandingPageSetting::get($key);
        }

        return view('panel.admin.landing_page', compact('settings'));
    }

    /**
     * Update Landing Page Settings.
     */
    public function updateLandingPage(Request $request)
    {
        $input = $request->except(['_token']);

        // Process features_list array if submitted
        if ($request->has('features_list_titles')) {
            $features = [];
            $titles = $request->input('features_list_titles', []);
            $descriptions = $request->input('features_list_descriptions', []);
            $icons = $request->input('features_list_icons', []);

            foreach ($titles as $idx => $title) {
                if (!empty($title)) {
                    $features[] = [
                        'title' => $title,
                        'description' => $descriptions[$idx] ?? '',
                        'icon' => $icons[$idx] ?? 'file',
                    ];
                }
            }
            LandingPageSetting::set('features_list', json_encode($features), 'features');
            unset($input['features_list_titles'], $input['features_list_descriptions'], $input['features_list_icons']);
        }

        // Process pricing_plans array if submitted
        if ($request->has('pricing_plan_names')) {
            $plans = [];
            $names = $request->input('pricing_plan_names', []);
            $monthly = $request->input('pricing_plan_monthly', []);
            $annual = $request->input('pricing_plan_annual', []);
            $taglines = $request->input('pricing_plan_taglines', []);
            $ctaLabels = $request->input('pricing_plan_cta_labels', []);
            $popularIdx = (int) $request->input('pricing_plan_popular', 1);
            $perksRaw = $request->input('pricing_plan_perks', []);

            foreach ($names as $idx => $name) {
                if (!empty($name)) {
                    $perksList = array_map('trim', explode("\n", $perksRaw[$idx] ?? ''));
                    $perksList = array_values(array_filter($perksList));

                    $plans[] = [
                        'name' => $name,
                        'price_monthly' => $monthly[$idx] ?? '$0',
                        'price_annual' => $annual[$idx] ?? '$0',
                        'period' => strtolower($name) === 'free' ? '/forever' : '/month',
                        'tagline' => $taglines[$idx] ?? '',
                        'popular' => ($idx == $popularIdx),
                        'cta_label' => $ctaLabels[$idx] ?? 'Get Started',
                        'perks' => $perksList,
                    ];
                }
            }
            LandingPageSetting::set('pricing_plans', json_encode($plans), 'pricing');
            unset(
                $input['pricing_plan_names'], $input['pricing_plan_monthly'], $input['pricing_plan_annual'],
                $input['pricing_plan_taglines'], $input['pricing_plan_cta_labels'], $input['pricing_plan_popular'], $input['pricing_plan_perks']
            );
        }

        // Group mapping for basic text keys
        $groups = [
            'hero' => ['hero_eyebrow', 'hero_title', 'hero_description', 'hero_cta_primary', 'hero_cta_secondary', 'hero_subtext', 'hero_mockup_url', 'hero_mockup_title', 'hero_mockup_sub'],
            'features' => ['features_title', 'features_subtitle'],
            'pricing' => ['pricing_title', 'pricing_subtitle'],
            'about' => ['about_title', 'about_description', 'founder_name', 'founder_title', 'founder_bio', 'founder_avatar'],
            'contact' => ['contact_title', 'contact_subtitle', 'contact_email', 'contact_hours', 'contact_location', 'contact_response_time', 'social_heading', 'social_github', 'social_twitter', 'social_linkedin'],

            'cta' => ['cta_title', 'cta_description', 'cta_button_text'],
        ];

        foreach ($input as $key => $val) {
            $group = 'general';
            foreach ($groups as $g => $keys) {
                if (in_array($key, $keys)) {
                    $group = $g;
                    break;
                }
            }
            LandingPageSetting::set($key, $val, $group);
        }

        return redirect()->back()->with('success', 'Landing page content updated successfully.');
    }

    /**
     * Reset Landing Page Settings to Factory Defaults.
     */
    public function resetLandingPage()
    {
        LandingPageSetting::seedDefaults(true);
        return redirect()->back()->with('success', 'Landing page content reset to factory defaults.');
    }

    /**
     * Global File Vault Manager.
     */
    public function allFiles(Request $request)
    {
        $query = FileModal::with('user')->where('is_trashed', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'image') $query->where('type', 'like', 'image/%');
            elseif ($type === 'video') $query->where('type', 'like', 'video/%');
            elseif ($type === 'audio') $query->where('type', 'like', 'audio/%');
            elseif ($type === 'pdf') $query->where('type', 'application/pdf');
            elseif ($type === 'code') $query->where(function ($q) {
                $q->where('name', 'like', '%.php')
                  ->orWhere('name', 'like', '%.js')
                  ->orWhere('name', 'like', '%.css')
                  ->orWhere('name', 'like', '%.html')
                  ->orWhere('name', 'like', '%.json');
            });
        }

        $files = $query->latest()->paginate(25)->withQueryString();
        $totalFileCount = FileModal::count();
        $totalFileBytes = FileModal::sum('size');

        return view('panel.admin.files', compact('files', 'totalFileCount', 'totalFileBytes'));
    }

    /**
     * Global Link Audit Console.
     */
    public function allLinks(Request $request)
    {
        $query = Links::with('user')->where('is_trashed', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('website_title', 'like', "%{$search}%")
                  ->orWhere('website_url', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $links = $query->latest()->paginate(25)->withQueryString();
        $totalLinkCount = Links::count();

        return view('panel.admin.links', compact('links', 'totalLinkCount'));
    }

    /**
     * Storage Pool Analytics & Quota Management.
     */
    public function storageAnalytics(Request $request)
    {
        $topStorageUsers = User::orderBy('storage_used', 'desc')->limit(10)->get();

        $totalUsedBytes = User::sum('storage_used');
        $totalQuotaBytes = User::sum('storage_quota');

        $imageBytes = FileModal::where('type', 'like', 'image/%')->sum('size');
        $videoBytes = FileModal::where('type', 'like', 'video/%')->sum('size');
        $audioBytes = FileModal::where('type', 'like', 'audio/%')->sum('size');
        $documentBytes = FileModal::where(function ($q) {
            $q->where('type', 'application/pdf')
                ->orWhere('type', 'like', '%word%')
                ->orWhere('type', 'like', '%excel%')
                ->orWhere('type', 'like', '%presentation%')
                ->orWhere('type', 'text/plain');
        })->sum('size');
        $otherBytes = max(0, $totalUsedBytes - ($imageBytes + $videoBytes + $audioBytes + $documentBytes));

        $stats = [
            'total_used_formatted' => $this->formatBytes($totalUsedBytes),
            'total_quota_formatted' => $this->formatBytes($totalQuotaBytes),
            'image_bytes_formatted' => $this->formatBytes($imageBytes),
            'video_bytes_formatted' => $this->formatBytes($videoBytes),
            'audio_bytes_formatted' => $this->formatBytes($audioBytes),
            'doc_bytes_formatted' => $this->formatBytes($documentBytes),
            'other_bytes_formatted' => $this->formatBytes($otherBytes),
            'storage_percentage' => $totalQuotaBytes > 0 ? round(($totalUsedBytes / $totalQuotaBytes) * 100, 2) : 0,
        ];

        return view('panel.admin.storage', compact('topStorageUsers', 'stats'));
    }

    /**
     * Batch Update User Quotas.
     */
    public function batchUpdateQuota(Request $request)
    {
        $quotaGb = (float) $request->input('quota_gb', 25);
        $quotaBytes = (int) ($quotaGb * 1024 * 1024 * 1024);
        $targetRole = $request->input('target_role', 'all');

        $query = User::query();
        if ($targetRole !== 'all') {
            $query->where(function ($q) use ($targetRole) {
                $q->where('role', $targetRole)
                  ->orWhere('account_type', $targetRole);
            });
        }

        $affectedCount = $query->update(['storage_quota' => $quotaBytes]);

        return redirect()->back()->with('success', "Successfully updated storage quota to {$quotaGb}GB for {$affectedCount} users.");
    }

    /**
     * Global System Settings.
     */
    public function systemSettings()
    {
        LandingPageSetting::seedDefaults();

        $settings = [
            'app_name' => LandingPageSetting::get('sys_app_name', config('app.name', 'FileFusion')),
            'default_quota_gb' => LandingPageSetting::get('sys_default_quota_gb', '25'),
            'max_upload_mb' => LandingPageSetting::get('sys_max_upload_mb', '500'),
            'items_per_page' => LandingPageSetting::get('sys_items_per_page', '12'),
            'maintenance_mode' => LandingPageSetting::get('sys_maintenance_mode', '0'),
            'registration_open' => LandingPageSetting::get('sys_registration_open', '1'),
            'support_email' => LandingPageSetting::get('contact_email', 'hello@filefusion.io'),
        ];

        // Prepare Role-specific storage quota defaults
        $roleQuotas = [];
        foreach (User::getAvailableRoles() as $roleKey => $roleName) {
            $roleQuotas[$roleKey] = [
                'label' => $roleName,
                'key' => 'sys_quota_role_' . $roleKey,
                'quota_gb' => User::getDefaultQuotaGbForRole($roleKey),
            ];
        }

        $keyStatus = \App\Services\KeyRotationService::getCurrentKeyStatus();

        return view('panel.admin.settings', compact('settings', 'keyStatus', 'roleQuotas'));
    }

    /**
     * Key Rotation: Run Pre-Flight Dry Run.
     */
    public function keyRotationDryRun(Request $request)
    {
        try {
            $dryRun = \App\Services\KeyRotationService::runDryRun();
            return response()->json([
                'ok' => $dryRun['success'] ? 1 : 0,
                'data' => $dryRun,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => 0,
                'message' => 'Dry run error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Key Rotation: Execute Live Re-encryption & Key Rotation.
     */
    public function executeKeyRotation(Request $request)
    {
        $rotateApp = $request->boolean('rotate_app_key', true);
        $rotateFile = $request->boolean('rotate_file_key', true);
        $keepPrevious = $request->boolean('keep_previous', true);
        $customAppKey = $request->input('custom_app_key');
        $customFileKey = $request->input('custom_file_key');

        try {
            $result = \App\Services\KeyRotationService::executeFullRotation([
                'rotate_app_key' => $rotateApp,
                'rotate_file_key' => $rotateFile,
                'new_app_key' => !empty($customAppKey) ? $customAppKey : null,
                'new_file_key' => !empty($customFileKey) ? $customFileKey : null,
                'keep_previous' => $keepPrevious,
                'force' => $request->boolean('force', false),
            ]);

            return response()->json([
                'ok' => $result['success'] ? 1 : 0,
                'data' => $result,
                'message' => $result['message'] ?? ($result['success'] ? 'Rotation completed successfully.' : 'Rotation failed.'),
            ]);
        } catch (Exception $e) {
            Log::error('Key rotation execution error: ' . $e->getMessage());
            return response()->json([
                'ok' => 0,
                'message' => 'Key rotation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Global System Settings.
     */
    public function updateSystemSettings(Request $request)
    {
        LandingPageSetting::set('sys_app_name', $request->input('app_name', 'FileFusion'), 'system');
        LandingPageSetting::set('sys_default_quota_gb', $request->input('default_quota_gb', '25'), 'system');
        LandingPageSetting::set('sys_max_upload_mb', $request->input('max_upload_mb', '500'), 'system');
        LandingPageSetting::set('sys_items_per_page', (string) $request->input('items_per_page', '12'), 'system');
        LandingPageSetting::set('sys_maintenance_mode', $request->has('maintenance_mode') ? '1' : '0', 'system');
        LandingPageSetting::set('sys_registration_open', $request->has('registration_open') ? '1' : '0', 'system');
        LandingPageSetting::set('contact_email', $request->input('support_email', 'hello@filefusion.io'), 'contact');

        // Save Role-specific default quotas and optional bulk sync
        $syncedCounts = [];
        foreach (User::getAvailableRoles() as $roleKey => $roleName) {
            $inputKey = 'quota_role_' . $roleKey;
            if ($request->has($inputKey)) {
                $val = (string) $request->input($inputKey);
                LandingPageSetting::set('sys_quota_role_' . $roleKey, $val, 'system');

                if ($request->boolean('sync_existing_role_' . $roleKey)) {
                    $bytes = (int) ((float) $val * 1024 * 1024 * 1024);
                    $updatedCount = User::where('role', $roleKey)->update(['storage_quota' => $bytes]);
                    $syncedCounts[] = "{$updatedCount} {$roleName}s ({$val}GB)";
                }
            }
        }

        $msg = 'Global system settings updated successfully.';
        if (!empty($syncedCounts)) {
            $msg .= ' Synced storage quotas for: ' . implode(', ', $syncedCounts) . '.';
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Email & SMTP System Settings.
     */
    public function emailSettings()
    {
        LandingPageSetting::seedDefaults();

        $settings = [
            'mail_mailer' => LandingPageSetting::get('mail_mailer', 'log'),
            'mail_host' => LandingPageSetting::get('mail_host', 'smtp.gmail.com'),
            'mail_port' => LandingPageSetting::get('mail_port', '587'),
            'mail_username' => LandingPageSetting::get('mail_username', ''),
            'mail_password' => LandingPageSetting::get('mail_password', ''),
            'mail_encryption' => LandingPageSetting::get('mail_encryption', 'tls'),
            'mail_from_address' => LandingPageSetting::get('mail_from_address', 'noreply@filefusion.io'),
            'mail_from_name' => LandingPageSetting::get('mail_from_name', 'FileFusion Workspace'),

            // Templates
            'email_tpl_file_shared_subject' => LandingPageSetting::get('email_tpl_file_shared_subject'),
            'email_tpl_file_shared_body' => LandingPageSetting::get('email_tpl_file_shared_body'),
            'email_tpl_file_deleted_subject' => LandingPageSetting::get('email_tpl_file_deleted_subject'),
            'email_tpl_file_deleted_body' => LandingPageSetting::get('email_tpl_file_deleted_body'),
            'email_tpl_account_created_subject' => LandingPageSetting::get('email_tpl_account_created_subject'),
            'email_tpl_account_created_body' => LandingPageSetting::get('email_tpl_account_created_body'),
            'email_tpl_totp_subject' => LandingPageSetting::get('email_tpl_totp_subject'),
            'email_tpl_totp_body' => LandingPageSetting::get('email_tpl_totp_body'),
            'email_tpl_forgot_pass_subject' => LandingPageSetting::get('email_tpl_forgot_pass_subject'),
            'email_tpl_forgot_pass_body' => LandingPageSetting::get('email_tpl_forgot_pass_body'),
            'email_tpl_storage_alert_subject' => LandingPageSetting::get('email_tpl_storage_alert_subject'),
            'email_tpl_storage_alert_body' => LandingPageSetting::get('email_tpl_storage_alert_body'),
            'email_tpl_trash_expiring_subject' => LandingPageSetting::get('email_tpl_trash_expiring_subject'),
            'email_tpl_trash_expiring_body' => LandingPageSetting::get('email_tpl_trash_expiring_body'),
        ];

        return view('panel.admin.email_settings', compact('settings'));
    }

    /**
     * Update Email & SMTP Settings.
     */
    public function updateEmailSettings(Request $request)
    {
        $input = $request->except(['_token']);

        foreach ($input as $key => $value) {
            $group = str_starts_with($key, 'mail_') ? 'smtp' : 'email_template';
            LandingPageSetting::set($key, (string) $value, $group);
        }

        return redirect()->back()->with('success', 'Email configuration and templates updated successfully.');
    }

    /**
     * Send test SMTP verification email with live form overrides.
     */
    public function sendTestEmail(Request $request)
    {
        $testEmail = $request->input('test_email', Auth::user()->email);
        $overrides = $request->only([
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
        ]);

        $cleanOverrides = array_filter($overrides, fn($v) => !is_null($v) && trim((string)$v) !== '');

        $result = \App\Helpers\MailHelper::testSmtpConnection($testEmail, $cleanOverrides);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => $result['success'] ? 1 : 0,
                'message' => $result['message'],
            ]);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * System Backups Management Page.
     */
    public function backupsIndex()
    {
        $backups = \App\Services\BackupService::listBackups();
        $totalBackupSizeBytes = array_sum(array_column($backups, 'size'));
        $totalBackupSizeFormatted = \App\Services\BackupService::formatBytes($totalBackupSizeBytes);

        $databaseSizeBytes = \App\Services\BackupService::getDatabaseSizeBytes();
        $databaseSizeFormatted = \App\Services\BackupService::formatBytes($databaseSizeBytes);

        $lastBackup = !empty($backups) ? $backups[0] : null;

        $stats = [
            'total_backups' => count($backups),
            'total_backup_size_formatted' => $totalBackupSizeFormatted,
            'database_size_formatted' => $databaseSizeFormatted,
            'last_backup_time' => $lastBackup ? $lastBackup['time_ago'] : 'Never',
            'last_backup_date' => $lastBackup ? $lastBackup['created_at'] : 'N/A',
        ];

        $remoteConfigs = \App\Services\RemoteStorageService::getAllConfigs();

        return view('panel.admin.backups', compact('backups', 'stats', 'remoteConfigs'));
    }

    /**
     * Create a new System Backup with optional remote offsite dispatch.
     */
    public function createBackup(Request $request)
    {
        $type = $request->input('type', 'full');
        $remoteTarget = $request->input('remote_target', 'none'); // 'none', 'ftp', 'sftp', 'both', 'auto'

        try {
            if ($type === 'codebase') {
                $result = \App\Services\BackupService::createCodebaseBackup();
                $msg = "Whole site & codebase backup archive created successfully ({$result['size_formatted']}, {$result['files_count']} files + Database).";
            } elseif ($type === 'database') {
                $result = \App\Services\BackupService::createDbBackup();
                $msg = "Database backup archive created successfully ({$result['size_formatted']}).";
            } elseif ($type === 'files') {
                $result = \App\Services\BackupService::createFilesBackup();
                $msg = "Files backup archive created successfully ({$result['size_formatted']}, {$result['files_count']} files).";
            } else {
                $result = \App\Services\BackupService::createFullBackup();
                $msg = "Full system backup created successfully ({$result['size_formatted']}, {$result['files_count']} files + Database).";
            }

            // Remote Offsite Dispatch
            $remoteResults = [];
            if ($remoteTarget !== 'none') {
                $remoteResults = \App\Services\RemoteStorageService::dispatchRemoteUploads($result['filename'], $remoteTarget);
                $dispatched = [];
                if (!empty($remoteResults['ftp']['success'])) $dispatched[] = 'FTP';
                if (!empty($remoteResults['sftp']['success'])) $dispatched[] = 'SFTP';
                if (!empty($dispatched)) {
                    $msg .= ' Uploaded to: ' . implode(' & ', $dispatched) . '.';
                }
            }

            \App\Services\AuditLogger::admin(
                'backup_created',
                "Generated {$type} backup: {$result['filename']} ({$result['size_formatted']})",
                'success',
                [
                    'type' => $type,
                    'filename' => $result['filename'],
                    'size' => $result['size'],
                    'checksum' => $result['checksum'],
                    'remote_results' => $remoteResults,
                ]
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => 1,
                    'message' => $msg,
                    'backup' => $result,
                    'remote' => $remoteResults,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            Log::error('Backup creation error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => 0,
                    'message' => 'Backup failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload an existing backup archive to Remote (FTP or SFTP).
     */
    public function uploadBackupToRemote(Request $request, string $filename)
    {
        $driver = $request->input('driver', 'ftp');

        try {
            $path = \App\Services\BackupService::getBackupPath($filename);
            if (!$path || !file_exists($path)) {
                return response()->json(['ok' => 0, 'message' => 'Backup file not found on disk.'], 404);
            }

            if ($driver === 'sftp') {
                $uploadRes = \App\Services\RemoteStorageService::uploadToSftp($path);
                $msg = "Successfully uploaded {$filename} to SFTP destination.";
            } else {
                $uploadRes = \App\Services\RemoteStorageService::uploadToFtp($path);
                $msg = "Successfully uploaded {$filename} to FTP destination.";
            }

            \App\Services\AuditLogger::admin(
                'backup_remote_upload',
                "Uploaded backup {$filename} to {$driver}",
                'success',
                ['filename' => $filename, 'driver' => $driver, 'result' => $uploadRes]
            );

            return response()->json([
                'ok' => 1,
                'message' => $msg,
                'data' => $uploadRes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => 0,
                'message' => "Offsite upload failed: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Remote Offsite Storage Configs (FTP, SFTP, Keep Local).
     */
    public function saveRemoteBackupConfig(Request $request)
    {
        \App\Services\RemoteStorageService::saveConfigs($request->all());

        \App\Services\AuditLogger::admin(
            'backup_config_updated',
            "Updated remote offsite backup configuration",
            'success'
        );

        return redirect()->back()->with('success', 'Remote offsite storage configuration saved successfully.');
    }

    /**
     * Test Remote Connection (FTP / SFTP).
     */
    public function testRemoteConnection(Request $request)
    {
        $driver = $request->input('driver', 'ftp');
        $config = $request->all();

        if ($driver === 'sftp') {
            $result = \App\Services\RemoteStorageService::testSftpConnection($config);
        } else {
            $result = \App\Services\RemoteStorageService::testFtpConnection($config);
        }

        return response()->json([
            'ok' => $result['success'] ? 1 : 0,
            'message' => $result['message'],
        ]);
    }

    /**
     * Download a Backup Archive.
     */
    public function downloadBackup(string $filename)
    {
        $path = \App\Services\BackupService::getBackupPath($filename);

        if (!$path || !file_exists($path)) {
            abort(404, 'Backup archive not found.');
        }

        \App\Services\AuditLogger::admin(
            'backup_downloaded',
            "Downloaded backup archive: {$filename}",
            'success',
            ['filename' => $filename]
        );

        return response()->download($path, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Delete a Backup Archive.
     */
    public function deleteBackup(Request $request, string $filename)
    {
        $deleted = \App\Services\BackupService::deleteBackup($filename);

        if ($deleted) {
            \App\Services\AuditLogger::admin(
                'backup_deleted',
                "Deleted backup archive: {$filename}",
                'success',
                ['filename' => $filename]
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => 1,
                    'message' => "Backup {$filename} deleted successfully.",
                ]);
            }

            return redirect()->back()->with('success', "Backup {$filename} deleted successfully.");
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => 0,
                'message' => "Failed to delete backup or file not found.",
            ], 404);
        }

        return redirect()->back()->with('error', "Failed to delete backup.");
    }
}



