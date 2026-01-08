<?php

use App\Http\Controllers\Api\ActionLogController;
use App\Http\Controllers\Api\AiContentController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\LocalLicenseController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\EmailSettingController;
use App\Http\Controllers\Api\Builder\MarkdownController;
use App\Http\Controllers\Backend\Api\TermController as BackendTermController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API endpoints.
Route::get('/translations/{lang}', function (string $lang) {
    $path = resource_path("lang/{$lang}.json");

    if (! file_exists($path)) {
        return response()->json(['error' => 'Language not found'], 404);
    }

    $translations = json_decode(file_get_contents($path), true);

    return response()->json($translations);
});

// Authentication routes.
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
    });
});

// Protected API routes.
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // User management.
    Route::apiResource('users', UserController::class);
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('api.users.bulk-delete');

    // Role management.
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/delete/bulk-delete', [RoleController::class, 'bulkDelete'])->name('api.roles.bulk-delete');

    // Permission management.
    Route::get('permissions', [PermissionController::class, 'index'])->name('api.permissions.index');
    Route::get('permissions/groups', [PermissionController::class, 'groups'])->name('api.permissions.groups');
    Route::get('permissions/{id}', [PermissionController::class, 'show'])->name('api.permissions.show');

    // Posts management (dynamic post types).
    Route::prefix('posts')->group(function () {
        Route::get('/{postType?}', [PostController::class, 'index'])->name('api.posts.index');
        Route::post('/{postType}', [PostController::class, 'store'])->name('api.posts.store');
        Route::get('/{postType}/{id}', [PostController::class, 'show'])->name('api.posts.show');
        Route::put('/{postType}/{id}', [PostController::class, 'update'])->name('api.posts.update');
        Route::delete('/{postType}/{id}', [PostController::class, 'destroy'])->name('api.posts.destroy');
        Route::post('/{postType}/bulk-delete', [PostController::class, 'bulkDelete'])->name('api.posts.bulk-delete');
    });

    // Terms management (Categories, Tags, etc.).
    Route::prefix('terms')->group(function () {
        Route::get('/{taxonomy}', [TermController::class, 'index'])->name('api.terms.index');
        Route::post('/{taxonomy}', [TermController::class, 'store'])->name('api.terms.store');
        Route::get('/{taxonomy}/{id}', [TermController::class, 'show'])->name('api.terms.show');
        Route::put('/{taxonomy}/{id}', [TermController::class, 'update'])->name('api.terms.update');
        Route::delete('/{taxonomy}/{id}', [TermController::class, 'destroy'])->name('api.terms.destroy');
        Route::post('/{taxonomy}/bulk-delete', [TermController::class, 'bulkDelete'])->name('api.terms.bulk-delete');
    });

    // Settings management.
    Route::get('settings', [SettingController::class, 'index'])->name('api.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('api.settings.update');
    Route::get('settings/{key}', [SettingController::class, 'show'])->name('api.settings.show');

    // Action logs.
    Route::get('action-logs', [ActionLogController::class, 'index'])->name('api.action-logs.index');
    Route::get('action-logs/{id}', [ActionLogController::class, 'show'])->name('api.action-logs.show');

    // AI Content Generation.
    Route::prefix('ai')->group(function () {
        Route::get('providers', [AiContentController::class, 'getProviders'])->name('api.ai.providers');
        Route::post('generate-content', [AiContentController::class, 'generateContent'])->name('api.ai.generate-content');
    });

    // Module management.
    Route::get('modules', [ModuleController::class, 'index'])->name('api.modules.index');
    Route::get('modules/{name}', [ModuleController::class, 'show'])->name('api.modules.show');
    Route::patch('modules/{name}/toggle-status', [ModuleController::class, 'toggleStatus'])->name('api.modules.toggle-status');
    Route::delete('modules/{name}', [ModuleController::class, 'destroy'])->name('api.modules.destroy');

    // Email templates.
    Route::apiResource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/by-type/{type}', [EmailTemplateController::class, 'getByType'])->name('api.email-templates.by-type');
    Route::get('email-templates/{email_template}/content', [EmailTemplateController::class, 'getContent'])->name('api.email-templates.content')->where('email_template', '[0-9]+');

    // Notifications.
    Route::apiResource('notifications', NotificationController::class);
    Route::get('notifications/by-type/{type}', [NotificationController::class, 'getByType'])->name('api.notifications.by-type');
    Route::get('notifications/by-receiver-type/{type}', [NotificationController::class, 'getByReceiverType'])->name('api.notifications.by-receiver-type');

    // Email settings endpoints.
    Route::get('email-settings', [EmailSettingController::class, 'index'])->name('api.email-settings.index');
    Route::put('email-settings', [EmailSettingController::class, 'update'])->name('api.email-settings.update');
});

// Admin API routes (for backward compatibility with existing web-based API calls).
Route::middleware(['auth', 'web'])->prefix('admin')->name('admin.api.')->group(function () {
    // Terms API (existing).
    Route::post('/terms/{taxonomy}', [BackendTermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{taxonomy}/{id}', [BackendTermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{taxonomy}/{id}', [BackendTermController::class, 'destroy'])->name('terms.destroy');

    // Local license storage (called after successful marketplace API activation).
    Route::prefix('licenses')->name('licenses.')->group(function () {
        Route::post('/store', [LocalLicenseController::class, 'store'])->name('store');
        Route::post('/remove', [LocalLicenseController::class, 'destroy'])->name('remove');
        Route::get('/show', [LocalLicenseController::class, 'show'])->name('show');
    });

    // Builder API routes.
    Route::prefix('builder')->name('builder.')->group(function () {
        Route::post('/markdown/fetch', [MarkdownController::class, 'fetch'])->name('markdown.fetch');
        Route::post('/markdown/convert', [MarkdownController::class, 'convert'])->name('markdown.convert');
        Route::post('/markdown/convert-url', [MarkdownController::class, 'convertUrl'])->name('markdown.convert-url');
    });
});
