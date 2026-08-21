<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
// Universal RESTful API v1 (Personal Access Token & MCP Interface)
// =========================================================================
Route::prefix('v1')->middleware(['api.token'])->group(function () {

    // Identity & Token Scopes
    Route::get('/auth/me', [\App\Http\Controllers\Api\ApiAuthController::class, 'me']);

    // File Management API
    Route::get('/files', [\App\Http\Controllers\Api\ApiFileController::class, 'index'])->middleware('api.token:files:read');
    Route::post('/files/upload', [\App\Http\Controllers\Api\ApiFileController::class, 'store'])->middleware('api.token:files:write');
    Route::post('/files/{id}/share', [\App\Http\Controllers\Api\ApiFileController::class, 'createShareLink'])->middleware('api.token:files:write');
    Route::get('/files/{id}/metadata', [\App\Http\Controllers\Api\ApiFileController::class, 'show'])->middleware('api.token:files:read');
    Route::get('/files/{id}/download', [\App\Http\Controllers\Api\ApiFileController::class, 'download'])->middleware('api.token:files:read');
    Route::delete('/files/{id}', [\App\Http\Controllers\Api\ApiFileController::class, 'destroy'])->middleware('api.token:files:delete');

    // Share Management API (Universal Shares Hub)
    Route::get('/shares', [\App\Http\Controllers\Api\ApiShareController::class, 'index'])->middleware('api.token:files:read');
    Route::post('/shares', [\App\Http\Controllers\Api\ApiShareController::class, 'store'])->middleware('api.token:files:write');
    Route::get('/shares/{id}', [\App\Http\Controllers\Api\ApiShareController::class, 'show'])->middleware('api.token:files:read');
    Route::put('/shares/{id}', [\App\Http\Controllers\Api\ApiShareController::class, 'update'])->middleware('api.token:files:write');
    Route::delete('/shares/{id}', [\App\Http\Controllers\Api\ApiShareController::class, 'destroy'])->middleware('api.token:files:delete');

    // Link & Bookmark API
    Route::get('/links', [\App\Http\Controllers\Api\ApiLinkController::class, 'index'])->middleware('api.token:links:read');
    Route::post('/links', [\App\Http\Controllers\Api\ApiLinkController::class, 'store'])->middleware('api.token:links:write');
    Route::get('/links/{id}', [\App\Http\Controllers\Api\ApiLinkController::class, 'show'])->middleware('api.token:links:read');
    Route::put('/links/{id}', [\App\Http\Controllers\Api\ApiLinkController::class, 'update'])->middleware('api.token:links:write');
    Route::delete('/links/{id}', [\App\Http\Controllers\Api\ApiLinkController::class, 'destroy'])->middleware('api.token:links:delete');

    // Password & Credential Vault API
    Route::get('/vault', [\App\Http\Controllers\Api\ApiPasswordController::class, 'index'])->middleware('api.token:vault:read');
    Route::post('/vault/reveal/{id}', [\App\Http\Controllers\Api\ApiPasswordController::class, 'reveal'])->middleware('api.token:vault:reveal');
    Route::post('/vault', [\App\Http\Controllers\Api\ApiPasswordController::class, 'store'])->middleware('api.token:vault:write');
    Route::put('/vault/{id}', [\App\Http\Controllers\Api\ApiPasswordController::class, 'update'])->middleware('api.token:vault:write');
    Route::delete('/vault/{id}', [\App\Http\Controllers\Api\ApiPasswordController::class, 'destroy'])->middleware('api.token:vault:delete');

    // Category & Tag Management API
    Route::get('/categories', [\App\Http\Controllers\Api\ApiCategoryController::class, 'index'])->middleware('api.token:categories:read');
    Route::post('/categories', [\App\Http\Controllers\Api\ApiCategoryController::class, 'store'])->middleware('api.token:categories:write');
    Route::get('/categories/{id}', [\App\Http\Controllers\Api\ApiCategoryController::class, 'show'])->middleware('api.token:categories:read');
    Route::put('/categories/{id}', [\App\Http\Controllers\Api\ApiCategoryController::class, 'update'])->middleware('api.token:categories:write');
    Route::delete('/categories/{id}', [\App\Http\Controllers\Api\ApiCategoryController::class, 'destroy'])->middleware('api.token:categories:delete');

    // To-Do & Task Management API
    Route::get('/todos', [\App\Http\Controllers\Api\ApiTodoController::class, 'index'])->middleware('api.token:todos:read');
    Route::post('/todos', [\App\Http\Controllers\Api\ApiTodoController::class, 'store'])->middleware('api.token:todos:write');
    Route::get('/todos/{id}', [\App\Http\Controllers\Api\ApiTodoController::class, 'show'])->middleware('api.token:todos:read');
    Route::put('/todos/{id}', [\App\Http\Controllers\Api\ApiTodoController::class, 'update'])->middleware('api.token:todos:write');
    Route::delete('/todos/{id}', [\App\Http\Controllers\Api\ApiTodoController::class, 'destroy'])->middleware('api.token:todos:delete');

    // Admin & Metrics API (Admin/Super Admin only)
    Route::get('/admin/users', [\App\Http\Controllers\Api\ApiAdminController::class, 'users'])->middleware('api.token:admin:users');
    Route::post('/admin/users', [\App\Http\Controllers\Api\ApiAdminController::class, 'storeUser'])->middleware('api.token:admin:users');
    Route::get('/admin/system/health', [\App\Http\Controllers\Api\ApiAdminController::class, 'health'])->middleware('api.token:admin:system');
});
