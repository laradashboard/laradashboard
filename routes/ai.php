<?php

use App\Http\Controllers\Mcp\McpMediaUploadController;
use App\Http\Controllers\Mcp\McpOAuthAuthorizationController;
use App\Http\Controllers\Mcp\McpOAuthClientController;
use App\Http\Controllers\Mcp\McpOAuthMetadataController;
use App\Http\Controllers\Mcp\McpOAuthTokenController;
use App\Http\Middleware\AuthenticateMcpAgent;
use App\Http\Middleware\EnsureMcpEnabled;
use App\Mcp\Servers\LaraDashboardServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| LaraDashboard MCP Routes
|--------------------------------------------------------------------------
|
| MCP HTTP endpoint for external AI agents (Cursor, Claude Desktop, etc.).
| Disabled by default via Settings > MCP. When disabled, EnsureMcpEnabled
| returns 404 so agents cannot connect.
|
*/

$mcpAgentMiddleware = [
    EnsureMcpEnabled::class,
    'auth:sanctum',
    AuthenticateMcpAgent::class,
];

Mcp::web('/mcp', LaraDashboardServer::class)
    ->middleware($mcpAgentMiddleware);

Route::post('/mcp/media/upload/{uploadToken}', [McpMediaUploadController::class, 'storeSigned'])
    ->middleware([EnsureMcpEnabled::class, 'signed'])
    ->name('mcp.media.upload.signed');

Route::middleware($mcpAgentMiddleware)->group(function (): void {
    Route::post('/mcp/media/upload', [McpMediaUploadController::class, 'store'])
        ->name('mcp.media.upload.store');
});

/*
|--------------------------------------------------------------------------
| OAuth 2.1 for remote MCP clients
|--------------------------------------------------------------------------
|
| Lets clients that only speak OAuth (e.g. Claude.ai custom connectors)
| connect with just the MCP URL: they discover these endpoints from the
| 401 response, register themselves, and the user approves in the browser.
| Issued access tokens are regular MCP agent tokens.
|
| The protected-resource route name is the one laravel/mcp's
| AddWwwAuthenticateHeader looks for when building the 401 header.
|
*/

Route::middleware(EnsureMcpEnabled::class)->group(function (): void {
    Route::get('/.well-known/oauth-protected-resource/{path?}', [McpOAuthMetadataController::class, 'protectedResource'])
        ->where('path', '.*')
        ->name('mcp.oauth.protected-resource.nested');

    Route::get('/.well-known/oauth-authorization-server/{path?}', [McpOAuthMetadataController::class, 'authorizationServer'])
        ->where('path', '.*')
        ->name('mcp.oauth.authorization-server');

    Route::post('/mcp/oauth/register', [McpOAuthClientController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('mcp.oauth.register');

    Route::post('/mcp/oauth/token', [McpOAuthTokenController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('mcp.oauth.token');

    Route::middleware(['web', 'auth'])->group(function (): void {
        Route::get('/mcp/oauth/authorize', [McpOAuthAuthorizationController::class, 'show'])
            ->name('mcp.oauth.authorize');

        Route::post('/mcp/oauth/authorize', [McpOAuthAuthorizationController::class, 'store'])
            ->name('mcp.oauth.authorize.store');
    });
});
