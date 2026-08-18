<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\WebsiteController;
use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;

// Dynamic PWA Manifest & Web Share Target
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/site.webmanifest', [PwaController::class, 'manifest']);
Route::match(['get', 'post'], '/pwa/share-target', [PwaController::class, 'shareTarget'])->name('pwa.shareTarget');

Route::get('/', function () {
    return view('index');
})->name('home');
Route::get('/about', function () {
    return view('about');
})->name('about');
Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');
Route::get('/contact', function () {
    return view('contact');
})->name('contact');
Route::get('/features', function () {
    return view('features');
})->name('features');

// Test Email Route (Remove in production)
Route::get('/test-email', function () {
    // Create a dummy user object for testing
    $testUser = new \App\Models\User();
    $testUser->id = 999;
    $testUser->name = 'John Doe';
    $testUser->email = 'test@example.com';

    return new \App\Mail\WelcomeMail($testUser);
});

// Login & 2FA Routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/login', [AuthController::class, 'login'])->name('loginaction');
Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('guest')->name('password.update');

// Two-Factor Challenge Routes
Route::get('/login/2fa', [TwoFactorController::class, 'challengeView'])->name('two-factor.challenge');
Route::post('/login/2fa', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
Route::post('/2fa/email-otp', [TwoFactorController::class, 'sendEmailOtp'])->middleware(['throttle:5,1'])->name('two-factor.email-otp');

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/email/verification-notice', [AuthController::class, 'verificationNotice'])
    ->name('verification.notice');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['throttle:6,1'])
    ->name('verification.send');



// Public File Sharing Routes
Route::get('/s/{token}', [ShareController::class, 'publicShareView'])->middleware(['throttle:60,1'])->name('public.share.view');
Route::post('/s/{token}/download', [ShareController::class, 'publicShareDownload'])->middleware(['throttle:15,1'])->name('public.share.download');


// Panel Routes
Route::prefix('panel')->name('panel.')->middleware([Auth::class])->group(function () {
    Route::get('/ashish', [WebsiteController::class, 'ashish'])->name('ashish');
    Route::get('/search', [WebsiteController::class, 'globalSearch'])->name('globalSearch');
    Route::get('/getWebScreenshot/{website_url?}', [WebsiteController::class, 'getWebScreenshot'])->name('getWebScreenshot');

    Route::get('/', [WebsiteController::class, 'dashboard'])->name('dashboard');
    Route::any('/files', [WebsiteController::class, 'filelist'])->name('filelist');
    Route::get('/upload', [WebsiteController::class, 'uploadfile'])->name('uploadfile');
    Route::post('/upload', [FileController::class, 'uploadaction'])->name('uploadaction');
    Route::get('/delete/{id}', [FileController::class, 'delete'])->name('deletefile');

    Route::get('/download/file/{fileid}', [FileController::class, 'download'])->name('downloadFile');
    Route::get('/preview/file/{fileid}', [FileController::class, 'preview'])->name('previewFile');
    Route::get('/preview/csv/{fileid}', [FileController::class, 'csvData'])->name('previewCsv');
    Route::get('/previewmodal', [WebsiteController::class, 'previewmodal'])->name('previewmodal');

    Route::get('/newfile', [WebsiteController::class, 'newfile'])->name('newfile');
    Route::post('/savefile', [FileController::class, 'createfile'])->name('createfile');
    Route::get('/edit-file/{fileId}', [WebsiteController::class, 'newfile'])->name('editFile');
    Route::post('/rename-file', [FileController::class, 'renameFile'])->name('renameFile');
    Route::post('/toggle-hide', [FileController::class, 'toggleHide'])->name('toggleHide');
    Route::post('/load-file-content', [FileController::class, 'loadFileContent'])->name('loadFileContent');
    Route::post('/bulk-action-files', [FileController::class, 'bulkAction'])->name('bulkActionFiles');

    // Hidden Files Routes & Unified Session Extension
    Route::get('/hidden-files-login', [FileController::class, 'hiddenFilesLogin'])->name('hiddenFilesLogin');
    Route::post('/hidden-files-auth', [FileController::class, 'hiddenFilesAuth'])->middleware(['throttle:5,1'])->name('hiddenFilesAuth');
    Route::get('/hidden-files', [FileController::class, 'hiddenFiles'])->name('hiddenFiles');
    Route::post('/logout-hidden-files', [FileController::class, 'logoutHiddenFiles'])->name('logoutHiddenFiles');
    Route::post('/vault/extend-session', [FileController::class, 'extendHiddenFilesSession'])->name('vault.extendSession');
    Route::post('/extend-hidden-files-session', [FileController::class, 'extendHiddenFilesSession'])->name('extendHiddenFilesSession');
    Route::post('/extend-hidden-links-session', [FileController::class, 'extendHiddenFilesSession'])->name('extendHiddenLinksSession');
    Route::post('/extend-hidden-passwords-session', [FileController::class, 'extendHiddenFilesSession'])->name('extendHiddenPasswordsSession');

    // Two-Factor Authentication Management
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/preferences', [TwoFactorController::class, 'updatePreferences'])->name('2fa.preferences');
    Route::get('/2fa/recovery-codes', [TwoFactorController::class, 'getRecoveryCodes'])->name('2fa.recoveryCodes.get');
    Route::post('/2fa/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.recoveryCodes');

    Route::get('/links', [LinkController::class, 'index'])->name('linklist');
    Route::get('/add-links', [LinkController::class, 'create'])->name('addlinkview');
    Route::post('/add-links', [LinkController::class, 'store'])->name('addlink');
    Route::get('/edit-link/{id}', [LinkController::class, 'edit'])->name('editlink');
    Route::post('/update-link/{id}', [LinkController::class, 'update'])->name('updatelink');
    Route::post('/toggle-star-link', [LinkController::class, 'toggleStar'])->name('toggleStarLink');
    Route::post('/toggle-hide-link', [LinkController::class, 'toggleHide'])->name('toggleHideLink');
    Route::get('/delete-link/{id}', [LinkController::class, 'delete'])->name('deletelink');
    Route::post('/links/recapture-screenshot', [LinkController::class, 'recaptureScreenshot'])->name('links.recapture_screenshot');
    Route::post('/bulk-action-links', [LinkController::class, 'bulkAction'])->name('bulkActionLinks');
    Route::post('/links/import', [LinkController::class, 'importBatch'])->name('links.import');
    Route::post('/links/export', [LinkController::class, 'exportData'])->name('links.export');

    // Hidden Links Routes
    Route::get('/hidden-links-login', [LinkController::class, 'hiddenLinksLogin'])->name('hiddenLinksLogin');
    Route::post('/hidden-links-auth', [LinkController::class, 'hiddenLinksAuth'])->middleware(['throttle:5,1'])->name('hiddenLinksAuth');
    Route::get('/hidden-links', [LinkController::class, 'hiddenLinks'])->name('hiddenLinks');
    Route::post('/unhide-link', [LinkController::class, 'unhideLink'])->name('unhideLink');
    Route::post('/logout-hidden-links', [LinkController::class, 'logoutHiddenLinks'])->name('logoutHiddenLinks');
    Route::post('/extend-hidden-links-session', [LinkController::class, 'extendSession'])->name('extendHiddenLinksSession');

    // Password / credential vault routes
    Route::get('/passwords', [PasswordController::class, 'index'])->name('passwords');
    Route::get('/add-password', [PasswordController::class, 'create'])->name('addpasswordview');
    Route::post('/add-password', [PasswordController::class, 'store'])->name('addpassword');
    Route::get('/edit-password/{id}', [PasswordController::class, 'edit'])->name('editpassword');
    Route::post('/update-password/{id}', [PasswordController::class, 'update'])->name('updatepassword');
    Route::post('/delete-password/{id}', [PasswordController::class, 'destroy'])->name('deletepassword');
    Route::post('/toggle-hide-password', [PasswordController::class, 'toggleHide'])->name('toggleHidePassword');
    Route::post('/passwords/reveal/{id}', [PasswordController::class, 'revealSecret'])->middleware(['throttle:30,1'])->name('passwords.reveal');
    Route::post('/passwords/verify-reveal-auth', [PasswordController::class, 'verifyRevealAuth'])->middleware(['throttle:15,1'])->name('passwords.verifyRevealAuth');
    Route::post('/passwords/import', [PasswordController::class, 'importBatch'])->name('passwords.import');
    Route::post('/passwords/export', [PasswordController::class, 'exportData'])->name('passwords.export');

    // Hidden Passwords Routes
    Route::get('/hidden-passwords-login', [PasswordController::class, 'hiddenLogin'])->name('hiddenPasswordsLogin');
    Route::post('/hidden-passwords-auth', [PasswordController::class, 'hiddenAuth'])->middleware(['throttle:5,1'])->name('hiddenPasswordsAuth');
    Route::get('/hidden-passwords', [PasswordController::class, 'hidden'])->name('hiddenPasswords');
    Route::post('/logout-hidden-passwords', [PasswordController::class, 'logoutHidden'])->name('logoutHiddenPasswords');

    // Category Routes
    Route::resource('categories', CategoryController::class);
    Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');



    // Profile & Settings Routes
    Route::get('/profile', [SettingsController::class, 'settings'])->name('profile');
    Route::get('/settings', [SettingsController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [SettingsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');
    Route::post('/settings/remove-avatar', [SettingsController::class, 'removeAvatar'])->name('settings.removeAvatar');
    Route::get('/user/avatar/{id?}', [SettingsController::class, 'getAvatar'])->name('user.avatar');
    Route::post('/settings/change-password', [SettingsController::class, 'updatePassword'])->name('settings.update.password');
    Route::post('/settings/per-page', [SettingsController::class, 'updatePerPage'])->name('settings.updatePerPage');




    Route::get('/trash/{type}', [WebsiteController::class, 'trashview'])->name('trashview');

    // File Trash Operations
    Route::get('/restore-file/{id}', [FileController::class, 'restore'])->name('restoreFile');
    Route::get('/permanent-delete-file/{id}', [FileController::class, 'permanentDelete'])->name('permanentDeleteFile');
    Route::get('/list-trash-files', [FileController::class, 'listTrashedFiles'])->name('listTrashedFiles');
    Route::any('/empty-trash', [FileController::class, 'emptyTrash'])->name('emptyTrash');

    // Link Trash Operations
    Route::post('/restore-link/{id}', [LinkController::class, 'restore'])->name('restoreLink');
    Route::post('/permanent-delete-link/{id}', [LinkController::class, 'permanentDelete'])->name('permanentDeleteLink');
    Route::post('/empty-links-trash', [LinkController::class, 'emptyTrash'])->name('emptyLinksTrash');

    // Password Trash Operations
    Route::post('/restore-password/{id}', [PasswordController::class, 'restore'])->name('restorePassword');
    Route::post('/permanent-delete-password/{id}', [PasswordController::class, 'permanentDelete'])->name('permanentDeletePassword');
    Route::post('/empty-passwords-trash', [PasswordController::class, 'emptyTrash'])->name('emptyPasswordsTrash');

    // File Sharing Routes
    Route::get('/shared-with-me', [ShareController::class, 'sharedWithMe'])->name('sharedWithMe');
    Route::post('/share/private', [ShareController::class, 'createPrivateShare'])->name('share.private');
    Route::post('/share/public', [ShareController::class, 'createOrUpdatePublicShare'])->name('share.public');
    Route::post('/share/revoke/{id}', [ShareController::class, 'revokeShare'])->name('share.revoke');

    // Dynamic Modals Link
    Route::any('/share/modal', [WebsiteController::class, 'sharemodal'])->name('sharemodal');
    Route::any('/delete/modal', [WebsiteController::class, 'deletemodal'])->name('deletemodal');

    // Impersonation Return Route
    Route::get('/admin/stop-impersonation', [AdminController::class, 'stopImpersonation'])->name('admin.stopImpersonation');

    // Super Admin Protected Routes
    Route::prefix('admin')->name('admin.')->middleware(['super_admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users/update/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/role/{id}', [AdminController::class, 'updateRole'])->name('users.role');
        Route::post('/users/reset-password/{id}', [AdminController::class, 'resetPassword'])->name('users.resetPassword');
        Route::post('/users/quota/{id}', [AdminController::class, 'updateQuota'])->name('users.quota');
        Route::post('/users/toggle-status/{id}', [AdminController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::post('/users/delete/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/users/impersonate/{id}', [AdminController::class, 'impersonateUser'])->name('users.impersonate');

        // Landing Page CMS Engine
        Route::get('/landing-page', [AdminController::class, 'landingPageEditor'])->name('landingPage');
        Route::post('/landing-page/update', [AdminController::class, 'updateLandingPage'])->name('landingPage.update');
        Route::post('/landing-page/reset', [AdminController::class, 'resetLandingPage'])->name('landingPage.reset');

        // Global Files Vault Manager
        Route::get('/files', [AdminController::class, 'allFiles'])->name('files');

        // Global Links Audit
        Route::get('/links', [AdminController::class, 'allLinks'])->name('links');

        // Storage Pool Analytics & Batch Quotas
        Route::get('/storage', [AdminController::class, 'storageAnalytics'])->name('storage');
        Route::post('/storage/batch-quota', [AdminController::class, 'batchUpdateQuota'])->name('storage.batchQuota');

        // Global System Settings
        Route::get('/settings', [AdminController::class, 'systemSettings'])->name('settings');
        Route::post('/settings/update', [AdminController::class, 'updateSystemSettings'])->name('settings.update');

        // System Storage Cleaner Engine
        Route::get('/storage-cleaner', [WebsiteController::class, 'cleanStoragePage'])->name('cleanStoragePage');
        Route::any('/cleanStorage', [WebsiteController::class, 'cleanStorage'])->name('cleanStorage');
        Route::get('/scanStorage', [WebsiteController::class, 'scanStorage'])->name('scanStorage');


        // Email & SMTP System Settings
        Route::get('/email-settings', [AdminController::class, 'emailSettings'])->name('emailSettings');
        Route::post('/email-settings/update', [AdminController::class, 'updateEmailSettings'])->name('emailSettings.update');
        Route::post('/email-settings/test', [AdminController::class, 'sendTestEmail'])->name('emailSettings.test');

        // Security & Activity Audit Trail
        Route::get('/activity-logs', [AuditLogController::class, 'index'])->name('activityLogs');
        Route::get('/activity-logs/export', [AuditLogController::class, 'exportCsv'])->name('activityLogs.export');

        // Master Encryption Key Rotation & Leak Recovery
        Route::post('/key-rotation/dry-run', [AdminController::class, 'keyRotationDryRun'])->name('keyRotation.dryRun');
        Route::post('/key-rotation/execute', [AdminController::class, 'executeKeyRotation'])->name('keyRotation.execute');

        // System & Database Backups
        Route::get('/backups', [AdminController::class, 'backupsIndex'])->name('backups');
        Route::post('/backups/create', [AdminController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/download/{filename}', [AdminController::class, 'downloadBackup'])->name('backups.download');
        Route::delete('/backups/delete/{filename}', [AdminController::class, 'deleteBackup'])->name('backups.delete');
        Route::post('/backups/upload-remote/{filename}', [AdminController::class, 'uploadBackupToRemote'])->name('backups.uploadRemote');
        Route::post('/backups/config/remote', [AdminController::class, 'saveRemoteBackupConfig'])->name('backups.config.remote');
        Route::post('/backups/test-remote', [AdminController::class, 'testRemoteConnection'])->name('backups.testRemote');
    });

    Route::get('/user/activity-logs', [AuditLogController::class, 'userActivity'])->name('user.activityLogs');


    Route::get('/storage-cleaner', [WebsiteController::class, 'cleanStoragePage'])->middleware(['super_admin'])->name('cleanStoragePage');
    Route::any('/cleanStorage', [WebsiteController::class, 'cleanStorage'])->middleware(['super_admin'])->name('cleanStorage');
    Route::get('/scanStorage', [WebsiteController::class, 'scanStorage'])->middleware(['super_admin'])->name('scanStorage');

});




