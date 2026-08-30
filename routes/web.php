<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\WebsiteController;
use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;

// Dynamic PWA Manifest & Web Share Target
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/site.webmanifest', [PwaController::class, 'manifest']);
Route::match(['get', 'post'], '/pwa/share-target', [PwaController::class, 'shareTarget'])->name('pwa.shareTarget');

// Background Automated Cron Routes
Route::prefix('cron')->name('cron.')->group(function () {
    Route::match(['get', 'post'], '/master', [CronController::class, 'master'])->name('master');
    Route::match(['get', 'post'], '/run', [CronController::class, 'run'])->name('run');
    Route::match(['get', 'post'], '/all', [CronController::class, 'run'])->name('all');
    Route::match(['get', 'post'], '/todos', [CronController::class, 'todoDeadlines'])->name('todos');
    Route::match(['get', 'post'], '/todo-deadlines', [CronController::class, 'todoDeadlines'])->name('todoDeadlines');
    Route::match(['get', 'post'], '/vault', [CronController::class, 'vaultSecurity'])->name('vault');
    Route::match(['get', 'post'], '/vault-check', [CronController::class, 'vaultSecurity'])->name('vaultCheck');
    Route::match(['get', 'post'], '/cleanup-chunks', [CronController::class, 'cleanupChunks'])->name('cleanupChunks');
    Route::match(['get', 'post'], '/cleanup-uploads', [CronController::class, 'cleanupChunks'])->name('cleanupUploads');
    Route::match(['get', 'post'], '/queue', [CronController::class, 'queueJobs'])->name('queue');
    Route::match(['get', 'post'], '/queue-work', [CronController::class, 'queueJobs'])->name('queueWork');
    Route::match(['get', 'post'], '/backup-db', [CronController::class, 'backupDb'])->name('backupDb');
    Route::match(['get', 'post'], '/backup-database', [CronController::class, 'backupDb'])->name('backupDatabase');
    Route::match(['get', 'post'], '/backup-full', [CronController::class, 'backupFull'])->name('backupFull');
    Route::match(['get', 'post'], '/backup-codebase', [CronController::class, 'backupFull'])->name('backupCodebase');
    Route::get('/status', [CronController::class, 'status'])->name('status');
});

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
Route::get('/api-tester', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user || !$user->isAdmin()) {
        abort(403, 'Unauthorized. Admin privileges required to access the API Developer Playground.');
    }
    return response(file_get_contents(public_path('api-tester.html')))->header('Content-Type', 'text/html');
})->middleware(['auth'])->name('api.tester');

Route::get('/api-console', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user || !$user->isAdmin()) {
        abort(403, 'Unauthorized. Admin privileges required to access the API Developer Playground.');
    }
    return response(file_get_contents(public_path('api-tester.html')))->header('Content-Type', 'text/html');
})->middleware(['auth']);

// Error Pages Showcase & Live Theme Preview
Route::get('/errors/preview/{code?}', function ($code = '404') {
    $allowedCodes = ['401', '403', '404', '500', '502'];
    $initialCode = in_array((string)$code, $allowedCodes, true) ? (string)$code : '404';
    return view('errors.preview', compact('initialCode'));
})->name('errors.preview');

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

// Universal Public Sharing Routes (Links, Passwords, Categories)
Route::get('/s/l/{token}', [\App\Http\Controllers\UniversalShareController::class, 'publicLinkView'])->middleware(['throttle:60,1'])->name('public.share.link.view');
Route::post('/s/l/{token}', [\App\Http\Controllers\UniversalShareController::class, 'publicLinkView'])->middleware(['throttle:60,1'])->name('public.share.link.unlock');
Route::get('/s/v/{token}', [\App\Http\Controllers\UniversalShareController::class, 'publicVaultView'])->middleware(['throttle:60,1'])->name('public.share.vault.view');
Route::post('/s/v/{token}/reveal', [\App\Http\Controllers\UniversalShareController::class, 'publicVaultReveal'])->middleware(['throttle:15,1'])->name('public.share.vault.reveal');
Route::get('/s/c/{token}', [\App\Http\Controllers\UniversalShareController::class, 'publicCategoryView'])->middleware(['throttle:60,1'])->name('public.share.category.view');
Route::post('/s/c/{token}', [\App\Http\Controllers\UniversalShareController::class, 'publicCategoryView'])->middleware(['throttle:60,1'])->name('public.share.category.unlock');

// PWA Manifest & Share Target Routes
Route::get('/manifest.json', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::post('/pwa/share-target', [\App\Http\Controllers\PwaController::class, 'shareTarget'])->name('pwa.share-target');

// Device Push Notification Routes (VAPID & FCM)
Route::get('/devices/vapid-public-key', [\App\Http\Controllers\DeviceController::class, 'getVapidPublicKey'])->name('devices.vapid_public_key');
Route::post('/devices/register-push', [\App\Http\Controllers\DeviceController::class, 'registerPush'])->name('devices.register_push');
Route::post('/devices/unregister-push', [\App\Http\Controllers\DeviceController::class, 'unregisterPush'])->name('devices.unregister_push');
Route::delete('/devices/{id}', [\App\Http\Controllers\DeviceController::class, 'deleteDevice'])->name('devices.delete');
Route::post('/devices/send-test-push', [\App\Http\Controllers\DeviceController::class, 'sendTestPush'])->name('devices.send_test_push');


// Panel Routes
Route::prefix('panel')->name('panel.')->middleware([Auth::class])->group(function () {
    Route::get('/ashish', [WebsiteController::class, 'ashish'])->name('ashish');
    Route::get('/search', [WebsiteController::class, 'globalSearch'])->name('globalSearch');
    Route::get('/search/check-hidden-status', [WebsiteController::class, 'checkHiddenStatus'])->name('search.checkHiddenStatus');
    Route::post('/search/verify-hidden-auth', [WebsiteController::class, 'verifySearchHiddenAuth'])->middleware(['throttle:15,1'])->name('search.verifyHiddenAuth');
    Route::get('/getWebScreenshot/{website_url?}', [WebsiteController::class, 'getWebScreenshot'])->name('getWebScreenshot');

    Route::get('/', [WebsiteController::class, 'dashboard'])->name('dashboard');
    Route::get('/about', [WebsiteController::class, 'aboutPage'])->name('about');
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
    Route::post('/vault/biometric-unlock', [FileController::class, 'biometricVaultUnlock'])->middleware(['throttle:15,1'])->name('vault.biometricUnlock');
    Route::get('/hidden-files', [FileController::class, 'hiddenFiles'])->name('hiddenFiles');
    Route::post('/logout-hidden-files', [FileController::class, 'logoutHiddenFiles'])->name('logoutHiddenFiles');
    Route::post('/vault/extend-session', [FileController::class, 'extendHiddenFilesSession'])->name('vault.extendSession');
    Route::post('/extend-hidden-files-session', [FileController::class, 'extendHiddenFilesSession'])->name('extendHiddenFilesSession');
    Route::post('/extend-hidden-links-session', [LinkController::class, 'extendHiddenLinksSession'])->name('extendHiddenLinksSession');
    Route::post('/extend-hidden-passwords-session', [PasswordController::class, 'extendHiddenPasswordsSession'])->name('extendHiddenPasswordsSession');

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

    // Secret Vault Global Auto-Lock & Beacon Invalidation
    Route::match(['get', 'post'], '/vault/lock', [WebsiteController::class, 'lockVault'])->name('vault.lock');
    Route::match(['get', 'post'], '/vault/lock-beacon', [WebsiteController::class, 'lockVaultBeacon'])->name('vault.lockBeacon');

    // Category Routes
    Route::get('/categories/secret-ajax', [CategoryController::class, 'secretCategoriesAjax'])->name('categories.secretAjax');
    Route::post('/categories/hidden-auth', [CategoryController::class, 'hiddenCategoriesAuth'])->middleware(['throttle:5,1'])->name('categories.hiddenAuth');
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
    Route::post('/share/update/{id}', [ShareController::class, 'updateShare'])->name('share.update');
    Route::post('/share/revoke/{id}', [ShareController::class, 'revokeShare'])->name('share.revoke');

    // Universal Sharing Creation, Update & Revocation Routes
    Route::post('/share/link', [\App\Http\Controllers\UniversalShareController::class, 'createLinkShare'])->name('share.link');
    Route::post('/share/link/update/{id}', [\App\Http\Controllers\UniversalShareController::class, 'updateLinkShare'])->name('share.link.update');
    Route::post('/share/link/revoke/{id}', [\App\Http\Controllers\UniversalShareController::class, 'revokeLinkShare'])->name('share.link.revoke');
    
    Route::post('/share/password', [\App\Http\Controllers\UniversalShareController::class, 'createPasswordShare'])->name('share.password');
    Route::post('/share/password/update/{id}', [\App\Http\Controllers\UniversalShareController::class, 'updatePasswordShare'])->name('share.password.update');
    Route::post('/share/password/revoke/{id}', [\App\Http\Controllers\UniversalShareController::class, 'revokePasswordShare'])->name('share.password.revoke');
    
    Route::post('/share/category', [\App\Http\Controllers\UniversalShareController::class, 'createCategoryShare'])->name('share.category');
    Route::post('/share/category/update/{id}', [\App\Http\Controllers\UniversalShareController::class, 'updateCategoryShare'])->name('share.category.update');
    Route::post('/share/category/revoke/{id}', [\App\Http\Controllers\UniversalShareController::class, 'revokeCategoryShare'])->name('share.category.revoke');

    // Todo & Task Management Workspace Routes
    Route::prefix('todos')->name('todos.')->group(function () {
        Route::get('/', [TodoController::class, 'index'])->name('index');
        Route::get('/calendar', [TodoController::class, 'calendarView'])->name('calendar');
        Route::get('/export/{format}', [TodoController::class, 'export'])->name('export');
        
        // Task operations
        Route::get('/tasks/{id}', [TodoController::class, 'showTask'])->name('tasks.show');
        Route::post('/tasks', [TodoController::class, 'storeTask'])->name('tasks.store');
        Route::put('/tasks/{id}', [TodoController::class, 'updateTask'])->name('tasks.update');
        Route::post('/tasks/{id}/toggle', [TodoController::class, 'toggleTask'])->name('tasks.toggle');
        Route::post('/tasks/{id}/star', [TodoController::class, 'toggleStar'])->name('tasks.star');
        Route::post('/tasks/{id}/dashboard-pin', [TodoController::class, 'toggleDashboardPin'])->name('tasks.dashboardPin');
        Route::delete('/tasks/{id}', [TodoController::class, 'destroyTask'])->name('tasks.destroy');
        
        // Sub-step checklist operations
        Route::post('/tasks/{id}/steps', [TodoController::class, 'storeStep'])->name('steps.store');
        Route::post('/steps/{stepId}/toggle', [TodoController::class, 'toggleStep'])->name('steps.toggle');
        Route::delete('/steps/{stepId}', [TodoController::class, 'destroyStep'])->name('steps.destroy');
        
        // Attachment operations
        Route::post('/tasks/{id}/attachments', [TodoController::class, 'uploadAttachment'])->name('attachments.upload');
        Route::delete('/tasks/{id}/attachments/{index}', [TodoController::class, 'deleteAttachment'])->name('attachments.delete');
        
        // Collection operations
        Route::post('/collections', [TodoController::class, 'storeCollection'])->name('collections.store');
        Route::put('/collections/{id}', [TodoController::class, 'updateCollection'])->name('collections.update');
        Route::delete('/collections/{id}', [TodoController::class, 'destroyCollection'])->name('collections.destroy');
    });

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
        Route::post('/users/toggle-api/{id}', [AdminController::class, 'toggleApiAccess'])->name('users.toggleApi');
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
        Route::post('/backups/config-remote', [AdminController::class, 'saveRemoteBackupConfig'])->name('backups.config.remote');
        Route::post('/backups/config-remote-save', [AdminController::class, 'saveRemoteBackupConfig'])->name('backups.configRemote');
        Route::post('/backups/test-remote', [AdminController::class, 'testRemoteConnection'])->name('backups.testRemote');
        Route::post('/backups/test-remote-connection', [AdminController::class, 'testRemoteConnection'])->name('backups.test.remote');
        // Mobile & Push Notification Testing Engine
        Route::get('/notifications', [AdminController::class, 'notificationsTester'])->name('notifications');
        Route::get('/notifications-tester', [AdminController::class, 'notificationsTester'])->name('notifications.tester');
    });

    Route::get('/user/activity-logs', [AuditLogController::class, 'userActivity'])->name('user.activityLogs');

    // Developer API Tokens Management
    Route::post('/developer/tokens', [\App\Http\Controllers\ApiTokenWebController::class, 'store'])->name('developer.tokens.store');
    Route::delete('/developer/tokens/{id}', [\App\Http\Controllers\ApiTokenWebController::class, 'destroy'])->name('developer.tokens.destroy');

    Route::get('/storage-cleaner', [WebsiteController::class, 'cleanStoragePage'])->middleware(['super_admin'])->name('cleanStoragePage');
    Route::any('/cleanStorage', [WebsiteController::class, 'cleanStorage'])->middleware(['super_admin'])->name('cleanStorage');
    Route::get('/scanStorage', [WebsiteController::class, 'scanStorage'])->middleware(['super_admin'])->name('scanStorage');

    // Multi-Device Push & VAPID Web Push Engine
    Route::get('/devices/vapid-public-key', [DeviceController::class, 'getVapidPublicKey'])->name('devices.vapidKey');
    Route::post('/devices/register-push', [DeviceController::class, 'registerPush'])->name('devices.registerPush');
    Route::post('/devices/unregister-push', [DeviceController::class, 'unregisterPush'])->name('devices.unregisterPush');
    Route::post('/devices/send-test-push', [DeviceController::class, 'sendTestPush'])->name('devices.sendTestPush');

});




