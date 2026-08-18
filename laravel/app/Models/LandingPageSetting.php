<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LandingPageSetting extends Model
{
    use HasFactory;

    protected $table = 'landing_page_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Default landing page configuration matching Landing Page.dc.html.
     */
    public static function defaults(): array
    {
        return [
            // HERO
            'hero_eyebrow' => 'File storage · Link vault · Categories',
            'hero_title' => 'One home for your files, links, and ideas.',
            'hero_description' => 'FileFusion unifies file storage, saved links, and organized categories into a single fast, private workspace — built for people who are done with digital clutter.',
            'hero_cta_primary' => 'Get Started Free',
            'hero_cta_secondary' => 'See how it works →',
            'hero_subtext' => 'No credit card required · 25GB free to start',
            'hero_mockup_url' => 'app.filefusion.io/dashboard',
            'hero_mockup_title' => 'Unified Workspace',
            'hero_mockup_sub' => 'Files, Encrypted Passwords & Link Vault in Sync',

            // FEATURES
            'features_title' => 'Everything in one place',
            'features_subtitle' => 'Six tools, one seamless workspace.',
            'features_list' => json_encode([
                [
                    'title' => 'Smart File Storage',
                    'description' => 'Upload, preview, and organize any file type with simple drag-and-drop envelope encryption.',
                    'icon' => 'file',
                ],
                [
                    'title' => 'Link Vault',
                    'description' => 'Save, tag, screenshot, and revisit web links without losing them in bookmark chaos.',
                    'icon' => 'link',
                ],
                [
                    'title' => 'Custom Categories',
                    'description' => 'Group files and links into organized collections that match how you actually work.',
                    'icon' => 'tag',
                ],
                [
                    'title' => 'Powerful Search',
                    'description' => 'Find anything instantly with global keyword indexing across files, saved links, and notes.',
                    'icon' => 'search',
                ],
                [
                    'title' => 'Effortless Sharing',
                    'description' => 'Share secure public download links with customizable expiration and password controls.',
                    'icon' => 'share',
                ],
                [
                    'title' => 'Vault-Grade Security',
                    'description' => 'AES-256 envelope encryption and a dedicated master vault password lock down your items.',
                    'icon' => 'shield',
                ],
            ]),

            // PRICING
            'pricing_title' => 'Simple, transparent pricing',
            'pricing_subtitle' => 'Start free. Upgrade only when you outgrow it.',
            'pricing_plans' => json_encode([
                [
                    'name' => 'Free',
                    'price_monthly' => '$0',
                    'price_annual' => '$0',
                    'period' => '/forever',
                    'tagline' => 'For getting organized solo.',
                    'popular' => false,
                    'cta_label' => 'Get Started',
                    'perks' => ['25GB storage', 'Unlimited saved links', '3 categories', 'Community support'],
                ],
                [
                    'name' => 'Pro',
                    'price_monthly' => '$9',
                    'price_annual' => '$7',
                    'period' => '/month',
                    'tagline' => 'For power users who live in their files.',
                    'popular' => true,
                    'cta_label' => 'Start Free Trial',
                    'perks' => ['250GB storage', 'Unlimited categories & links', 'Vault password protection', 'Priority support'],
                ],
                [
                    'name' => 'Team',
                    'price_monthly' => '$24',
                    'price_annual' => '$19',
                    'period' => '/month',
                    'tagline' => 'For teams sharing one workspace.',
                    'popular' => false,
                    'cta_label' => 'Start Free Trial',
                    'perks' => ['1TB pooled storage', 'Shared team categories', 'Advanced permissions', 'Dedicated onboarding'],
                ],
            ]),

            // ABOUT
            'about_title' => 'Why we built FileFusion',
            'about_description' => "Most people's digital life is scattered across a downloads folder, three bookmark managers, and a notes app they forgot the password to. We built FileFusion because organizing your files and links shouldn't take more effort than the work itself — everything you save should be exactly where you left it.",
            'about_values' => json_encode([
                [
                    'title' => 'Simplicity',
                    'description' => 'One workspace instead of five disconnected apps.',
                    'icon' => 'check',
                ],
                [
                    'title' => 'Privacy & Security',
                    'description' => 'Your files and links are yours — encrypted and private.',
                    'icon' => 'shield',
                ],
                [
                    'title' => 'Lightning Speed',
                    'description' => 'Built to feel instant, even with thousands of items.',
                    'icon' => 'zap',
                ],
            ]),
            'founder_name' => 'Ashish',
            'founder_title' => 'Founder & Lead Engineer',
            'founder_bio' => 'Passionate about crafting intuitive, high-performance web applications that make organizing digital lives effort-free.',
            'founder_avatar' => 'https://avatars.githubusercontent.com/u/67684653?v=4',

            // CONTACT
            'contact_title' => 'Get in touch',
            'contact_subtitle' => 'Questions, feedback, or partnership ideas — we read everything.',
            'contact_email' => 'hello@filefusion.io',
            'contact_hours' => 'Mon–Fri, 9am–6pm',
            'contact_location' => 'Delhi, India',
            'contact_response_time' => 'Average response time is under 4 hours on business days.',

            // SOCIAL / CONNECT WITH US
            'social_heading' => 'Connect With Us',
            'social_github' => 'https://github.com/ashishkumar2310',
            'social_twitter' => '#',
            'social_linkedin' => '#',

            // CTA
            'cta_title' => 'Ready to streamline your digital life?',
            'cta_description' => "Join a growing number of people who've stopped losing files and links in the shuffle.",
            'cta_button_text' => 'Get Started Free',

            // SMTP MAIL SETTINGS
            'mail_mailer' => 'log',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@filefusion.io',
            'mail_from_name' => 'FileFusion Workspace',

            // EMAIL TEMPLATE 1: FILE SHARED
            'email_tpl_file_shared_subject' => '{owner_name} shared a file with you: {file_name}',
            'email_tpl_file_shared_body' => '<p>Hello <strong>{recipient_name}</strong>,</p><p><strong>{owner_name}</strong> has shared a file with your account on FileFusion.</p><p><strong>File Name:</strong> {file_name}</p><div style="margin:24px 0;"><a href="{download_link}" style="background:#6366f1; color:#ffffff; padding:12px 24px; font-weight:bold; text-decoration:none; border-radius:8px; display:inline-block;">Access & Download File</a></div><p style="color:#666; font-size:13px;">If you were not expecting this file, you can safely ignore this notification.</p>',

            // EMAIL TEMPLATE 2: FILE DELETED REPORT
            'email_tpl_file_deleted_subject' => 'File Removal Report: {file_name}',
            'email_tpl_file_deleted_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>This report confirms that the following file was permanently removed from your account storage:</p><p><strong>File Name:</strong> {file_name}<br><strong>Removed By:</strong> {deleted_by}<br><strong>Reason:</strong> {reason}</p><p style="color:#666; font-size:13px;">This action was taken according to administrative policy or storage management rules.</p>',

            // EMAIL TEMPLATE 3: ACCOUNT CREATED
            'email_tpl_account_created_subject' => 'Welcome to FileFusion - Account Created Successfully',
            'email_tpl_account_created_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>Your FileFusion account has been created successfully!</p><p><strong>Registered Email:</strong> {user_email}</p><div style="margin:24px 0;"><a href="{login_link}" style="background:#6366f1; color:#ffffff; padding:12px 24px; font-weight:bold; text-decoration:none; border-radius:8px; display:inline-block;">Log in to Your Dashboard</a></div><p>Start organizing your files, bookmarking links, and securing notes today.</p>',

            // EMAIL TEMPLATE 4: TOTP 2FA
            'email_tpl_totp_subject' => '{totp_code} is your FileFusion Security Code',
            'email_tpl_totp_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>Here is your one-time verification security code:</p><div style="font-size:32px; font-weight:bold; letter-spacing:6px; color:#6366f1; margin:20px 0;">{totp_code}</div><p style="color:#666; font-size:13px;">This code will expire in {expires_minutes} minutes. Never share this code with anyone.</p>',

            // EMAIL TEMPLATE 5: FORGOT PASSWORD
            'email_tpl_forgot_pass_subject' => 'Reset Your FileFusion Password',
            'email_tpl_forgot_pass_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>We received a request to reset your FileFusion account password.</p><div style="margin:24px 0;"><a href="{reset_link}" style="background:#e0392e; color:#ffffff; padding:12px 24px; font-weight:bold; text-decoration:none; border-radius:8px; display:inline-block;">Reset My Password</a></div><p style="color:#666; font-size:13px;">This link will expire in {expires_hours} hour(s). If you did not request a password reset, no further action is required.</p>',

            // EMAIL TEMPLATE 6A: STORAGE USAGE ALERT
            'email_tpl_storage_alert_subject' => 'Storage Capacity Alert ({percentage}% Used)',
            'email_tpl_storage_alert_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>Your storage usage has reached <strong>{percentage}%</strong> of your assigned quota.</p><p><strong>Storage Used:</strong> {used_storage} of {quota_storage}</p><p>To avoid upload disruptions, please consider removing old files or upgrading your storage tier.</p>',

            // EMAIL TEMPLATE 6B: TRASH RETENTION EXPIRING
            'email_tpl_trash_expiring_subject' => 'Warning: Trashed Files Deleting Soon ({file_count} Files)',
            'email_tpl_trash_expiring_body' => '<p>Hello <strong>{user_name}</strong>,</p><p>You have <strong>{file_count} file(s)</strong> in your Trash Can that are scheduled for permanent deletion on <strong>{deletion_date}</strong>.</p><p>If you wish to keep these files, please log in and restore them from your Trash Can prior to the expiration date.</p>',

        ];
    }


    /**
     * Get a setting by key with caching and default fallback.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("landing_setting_{$key}", 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if ($record && !is_null($record->value)) {
                return $record->value;
            }
            $defaults = static::defaults();
            return $defaults[$key] ?? $default;
        });
    }

    /**
     * Set a setting key and clear cache.
     */
    public static function set(string $key, $value, string $group = 'general')
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $record = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("landing_setting_{$key}");

        return $record;
    }

    /**
     * Seed default values if empty.
     */
    public static function seedDefaults(bool $force = false)
    {
        $defaults = static::defaults();
        $groups = [
            'hero' => ['hero_eyebrow', 'hero_title', 'hero_description', 'hero_cta_primary', 'hero_cta_secondary', 'hero_subtext', 'hero_mockup_url', 'hero_mockup_title', 'hero_mockup_sub'],
            'features' => ['features_title', 'features_subtitle', 'features_list'],
            'pricing' => ['pricing_title', 'pricing_subtitle', 'pricing_plans'],
            'about' => ['about_title', 'about_description', 'about_values', 'founder_name', 'founder_title', 'founder_bio', 'founder_avatar'],
            'contact' => ['contact_title', 'contact_subtitle', 'contact_email', 'contact_hours', 'contact_location', 'contact_response_time'],
            'cta' => ['cta_title', 'cta_description', 'cta_button_text'],
        ];

        foreach ($defaults as $key => $val) {
            $group = 'general';
            foreach ($groups as $g => $keys) {
                if (in_array($key, $keys)) {
                    $group = $g;
                    break;
                }
            }

            if ($force || !static::where('key', $key)->exists()) {
                static::set($key, $val, $group);
            }
        }
    }
}
