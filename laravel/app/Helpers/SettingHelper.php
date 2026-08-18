<?php

namespace App\Helpers;

use App\Models\LandingPageSetting;
use Illuminate\Support\Facades\Auth;

class SettingHelper
{
    /**
     * Get the number of items visible per page from persistent datastorage.
     *
     * Priority:
     * 1. Query parameter ?per_page=X (if valid integer between 1 and 200)
     * 2. Authenticated user's preference stored in users table (items_per_page)
     * 3. Global system default stored in landing_page_settings table (sys_items_per_page)
     * 4. Provided fallback default ($default)
     *
     * @param int $default
     * @return int
     */
    public static function getItemsPerPage(int $default = 12): int
    {
        // 1. Request query parameter override (e.g. ?per_page=24)
        $reqPerPage = request('per_page');
        if (!empty($reqPerPage) && is_numeric($reqPerPage)) {
            $val = (int) $reqPerPage;
            if ($val >= 1 && $val <= 200) {
                return $val;
            }
        }

        // 2. User database preference
        $user = Auth::user();
        if ($user && !empty($user->items_per_page) && $user->items_per_page > 0) {
            return (int) $user->items_per_page;
        }

        // 3. Global system setting in database
        $sysPerPage = LandingPageSetting::get('sys_items_per_page');
        if (!empty($sysPerPage) && is_numeric($sysPerPage) && $sysPerPage > 0) {
            return (int) $sysPerPage;
        }

        return $default;
    }

    /**
     * Set the items per page setting in persistent storage.
     *
     * @param int $perPage
     * @param bool $forCurrentUserOnly
     * @return void
     */
    public static function setItemsPerPage(int $perPage, bool $forCurrentUserOnly = false): void
    {
        $perPage = max(1, min(200, $perPage));

        if ($forCurrentUserOnly && Auth::check()) {
            $user = Auth::user();
            $user->items_per_page = $perPage;
            $user->save();
        } else {
            LandingPageSetting::set('sys_items_per_page', (string) $perPage, 'system');
        }
    }
}
