<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'role',
        'vault_pass',
        'enc_key',
        'nickname',
        'username',
        'status',
        'email_verified_at',
        'directory',
        'avatar',
        'remember_token',
        'storage_quota',
        'storage_used',
        'items_per_page',
        'api_access_enabled',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_type',
        'two_factor_enforce_login',
        'two_factor_enforce_vault',
        'two_factor_enforce_password_reveal',
        'vault_session_lifetime',
        'hidden_files_session_lifetime',
        'hidden_links_session_lifetime',
        'hidden_passwords_session_lifetime',
        'password_reveal_lifetime',
        'vault_biometric_enabled',
        'two_factor_recovery_codes',
        'email_otp_code',
        'email_otp_expires_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_otp_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'storage_quota' => 'integer',
            'storage_used' => 'integer',
            'api_access_enabled' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_enforce_login' => 'boolean',
            'two_factor_enforce_vault' => 'boolean',
            'two_factor_enforce_password_reveal' => 'boolean',
            'vault_biometric_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'email_otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Get or create the user's private storage directory identifier
     */
    public function getUserDirectory(): string
    {
        if (empty($this->directory)) {
            $this->directory = ($this->username ?: 'user_' . $this->id) . '-' . \Illuminate\Support\Str::uuid()->toString();
            $this->save();
        }
        return $this->directory;
    }

    /**
     * Check if user is approved to use REST API & generate Personal Access Tokens.
     */
    public function canUseApi(): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return (bool) $this->api_access_enabled && $this->isActive();
    }

    /**
     * Interact with the two_factor_secret attribute (AES-256 encrypted in database).
     */
    protected function twoFactorSecret(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Check if user has Two-Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->two_factor_enabled && !empty($this->two_factor_secret);
    }

    /**
     * Check if user requires 2FA for a specific action ('login', 'vault', 'password_reveal').
     */
    public function requiresTwoFactorFor(string $action): bool
    {
        if (!$this->hasTwoFactorEnabled()) {
            return false;
        }

        return match ($action) {
            'login' => (bool) $this->two_factor_enforce_login,
            'vault' => (bool) $this->two_factor_enforce_vault,
            'password_reveal' => (bool) $this->two_factor_enforce_password_reveal,
            default => true,
        };
    }

    /**
     * User's uploaded files relationship.
     */
    public function files()
    {
        return $this->hasMany(\App\Models\FileModal::class, 'user_id');
    }

    /**
     * User's saved links relationship.
     */
    public function links()
    {
        return $this->hasMany(\App\Models\Links::class, 'user_id');
    }

    /**
     * User's API Tokens relationship.
     */
    public function apiTokens()
    {
        return $this->hasMany(\App\Models\ApiToken::class, 'user_id');
    }

    /**
     * User's saved passwords relationship.
     */
    public function passwords()
    {
        return $this->hasMany(\App\Models\Password::class, 'user_id');
    }

    /**
     * Get storage used in human readable format
     */
    public function getStorageUsedFormatted()
    {
        return $this->formatBytes($this->storage_used);
    }

    /**
     * Get storage quota in human readable format
     */
    public function getStorageQuotaFormatted()
    {
        return $this->formatBytes($this->storage_quota);
    }

    /**
     * Get storage usage percentage
     */
    public function getStorageUsagePercentage()
    {
        if ($this->storage_quota == 0) {
            return 0;
        }
        return round(($this->storage_used / $this->storage_quota) * 100, 2);
    }

    /**
     * Check if user has enough storage
     */
    public function hasEnoughStorage($bytes)
    {
        return ($this->storage_used + $bytes) <= $this->storage_quota;
    }

    /**
     * Check if user has an uploaded encrypted avatar.
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    /**
     * Get avatar image streaming URL.
     */
    public function getAvatarUrl(): string
    {
        if ($this->hasAvatar()) {
            return route('panel.user.avatar', $this->id);
        }
        return '';
    }

    /**
     * Add storage usage & trigger threshold alerts (75%, 90%, 100%)
     */
    public function addStorageUsage($bytes)
    {
        $oldPercentage = $this->getStorageUsagePercentage();
        $this->storage_used += $bytes;
        $this->save();
        $newPercentage = $this->getStorageUsagePercentage();

        if (($oldPercentage < 75 && $newPercentage >= 75) || ($oldPercentage < 90 && $newPercentage >= 90) || ($oldPercentage < 100 && $newPercentage >= 100)) {
            \App\Helpers\MailHelper::sendStorageAlert($this, $newPercentage);
        }
    }


    /**
     * Reduce storage usage
     */
    public function reduceStorageUsage($bytes)
    {
        $this->storage_used = max(0, $this->storage_used - $bytes);
        $this->save();
    }

    public function subtractStorageUsage($bytes)
    {
        $this->reduceStorageUsage($bytes);
    }

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_PRO_USER = 'pro_user';
    public const ROLE_USER = 'user';

    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_USER => 'Standard User',
            self::ROLE_PRO_USER => 'Pro User',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPER_ADMIN => 'Super Admin',
        ];
    }

    /**
     * Get default storage quota in GB for a given role.
     */
    public static function getDefaultQuotaGbForRole(string $role): float
    {
        $defaults = [
            self::ROLE_USER => '25',
            self::ROLE_PRO_USER => '100',
            self::ROLE_MANAGER => '250',
            self::ROLE_ADMIN => '500',
            self::ROLE_SUPER_ADMIN => '1000',
        ];

        $fallbackGb = $defaults[$role] ?? '25';
        $settingKey = 'sys_quota_role_' . $role;
        $val = \App\Models\LandingPageSetting::get($settingKey, \App\Models\LandingPageSetting::get('sys_default_quota_gb', $fallbackGb));

        return (float) $val;
    }

    /**
     * Get default storage quota in bytes for a given role.
     */
    public static function getDefaultQuotaForRole(string $role): int
    {
        $gb = self::getDefaultQuotaGbForRole($role);
        return (int) ($gb * 1024 * 1024 * 1024);
    }

    /**
     * Check if user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return (string)$this->account_type === '1' || strtolower((string)$this->role) === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is an Admin or Super Admin.
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || strtolower((string)$this->role) === self::ROLE_ADMIN;
    }

    /**
     * Check if user is a Manager or above.
     */
    public function isManager(): bool
    {
        return $this->isAdmin() || strtolower((string)$this->role) === self::ROLE_MANAGER;
    }

    /**
     * Check if user is a Pro User or above.
     */
    public function isPro(): bool
    {
        return $this->isManager() || strtolower((string)$this->role) === self::ROLE_PRO_USER;
    }

    /**
     * Get display name for the user's role.
     */
    public function getRoleDisplayName(): string
    {
        $roles = self::getAvailableRoles();
        return $roles[$this->role] ?? 'Standard User';
    }

    /**
     * Update user role and synchronize account_type.
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
        $this->account_type = ($role === self::ROLE_SUPER_ADMIN) ? '1' : '2';
        $this->save();
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return (int)$this->status === 1;
    }

    /**
     * Set storage quota in Gigabytes (GB).
     */
    public function setStorageQuotaGB(float $gb): void
    {
        $this->storage_quota = (int) round($gb * 1024 * 1024 * 1024);
        $this->save();
    }

    /**
     * Format bytes to human readable format
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get configured Vault Session Lifetime in seconds (default 1800s / 30m, 0 means Ask Always).
     */
    public function getVaultSessionLifetime(): int
    {
        if ($this->vault_session_lifetime === 0 || $this->vault_session_lifetime === '0') {
            return 0; // Immediate / Ask Always
        }
        return (int) ($this->vault_session_lifetime ?? 1800);
    }

    /**
     * Get configured Hidden Files Session Lifetime in seconds (default 1800s / 30m, 0 means Ask Always).
     */
    public function getHiddenFilesSessionLifetime(): int
    {
        if ($this->hidden_files_session_lifetime === 0 || $this->hidden_files_session_lifetime === '0') {
            return 0; // Immediate / Ask Always
        }
        return (int) ($this->hidden_files_session_lifetime ?? $this->vault_session_lifetime ?? 1800);
    }

    /**
     * Get configured Hidden Links Session Lifetime in seconds (default 1800s / 30m, 0 means Ask Always).
     */
    public function getHiddenLinksSessionLifetime(): int
    {
        if ($this->hidden_links_session_lifetime === 0 || $this->hidden_links_session_lifetime === '0') {
            return 0; // Immediate / Ask Always
        }
        return (int) ($this->hidden_links_session_lifetime ?? $this->vault_session_lifetime ?? 1800);
    }

    /**
     * Get configured Hidden Passwords / Password Vault Session Lifetime in seconds (default 1800s / 30m, 0 means Ask Always).
     */
    public function getHiddenPasswordsSessionLifetime(): int
    {
        if ($this->hidden_passwords_session_lifetime === 0 || $this->hidden_passwords_session_lifetime === '0') {
            return 0; // Immediate / Ask Always
        }
        return (int) ($this->hidden_passwords_session_lifetime ?? $this->vault_session_lifetime ?? 1800);
    }

    /**
     * Get configured Password Reveal Secret Lifetime in seconds (default 900s / 15m, 0 means every time).
     */
    public function getPasswordRevealLifetime(): int
    {
        if ($this->password_reveal_lifetime === 0 || $this->password_reveal_lifetime === '0') {
            return 0; // Immediate / Every Time
        }
        return (int) ($this->password_reveal_lifetime ?? 900);
    }

    /**
     * Check if Fingerprint / Biometric authentication for Vaults is enabled by user.
     */
    public function isVaultBiometricEnabled(): bool
    {
        return (bool) ($this->vault_biometric_enabled ?? false);
    }
}


