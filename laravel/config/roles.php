<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role Tiers, Quotas, and Capabilities
    |--------------------------------------------------------------------------
    |
    | Defines default storage quotas, max upload sizes, and feature gates
    | for each user role in the platform.
    |
    */

    'roles' => [
        'user' => [
            'name' => 'Standard User',
            'badge_color' => '#71717a',
            'default_quota_gb' => 25,
            'max_upload_size_mb' => 500,
            'can_share_public' => true,
            'can_use_vault' => true,
            'can_create_links' => true,
        ],
        'pro_user' => [
            'name' => 'Pro User',
            'badge_color' => '#8b5cf6',
            'default_quota_gb' => 100,
            'max_upload_size_mb' => 2048, // 2 GB
            'can_share_public' => true,
            'can_use_vault' => true,
            'can_create_links' => true,
        ],
        'manager' => [
            'name' => 'Manager',
            'badge_color' => '#3b82f6',
            'default_quota_gb' => 500,
            'max_upload_size_mb' => 5120, // 5 GB
            'can_share_public' => true,
            'can_use_vault' => true,
            'can_create_links' => true,
        ],
        'admin' => [
            'name' => 'Admin',
            'badge_color' => '#ec4899',
            'default_quota_gb' => 1000, // 1 TB
            'max_upload_size_mb' => 10240, // 10 GB
            'can_share_public' => true,
            'can_use_vault' => true,
            'can_create_links' => true,
            'can_access_admin' => true,
        ],
        'super_admin' => [
            'name' => 'Super Admin',
            'badge_color' => '#6366f1',
            'default_quota_gb' => 5000, // 5 TB
            'max_upload_size_mb' => 51200, // 50 GB
            'can_share_public' => true,
            'can_use_vault' => true,
            'can_create_links' => true,
            'can_access_admin' => true,
            'is_root' => true,
        ],
    ],
];
