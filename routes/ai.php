<?php

use App\Http\Controllers\Mcp\McpMediaUploadController;
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
