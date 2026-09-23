<?php

declare(strict_types=1);

namespace App\Services\Mcp;

use App\Models\Setting;

class McpSettingsService
{
    public const CURSOR_MCP_ENV_VAR = 'LARADASHBOARD_MCP_TOKEN';

    public function isEnabled(): bool
    {
        return filter_var(config('settings.'.Setting::MCP_ENABLED, false), FILTER_VALIDATE_BOOLEAN);
    }

    public function serverUrl(): string
    {
        return url('/mcp');
    }

    public function multipartUploadUrl(): string
    {
        return route('mcp.media.upload.store');
    }

    public function isInsecureHttp(): bool
    {
        return ! str_starts_with($this->serverUrl(), 'https://');
    }

    public function defaultAiClient(): string
    {
        return 'claude_desktop';
    }

    /**
     * @return array<string, array{label: string, icon: string, config_paths: array<int, string>, menu_path: string}>
     */
    public function supportedClients(): array
    {
        return [
            'cursor' => [
                'label' => __('Cursor (Desktop & CLI)'),
                'icon' => 'lucide:mouse-pointer-2',
                'config_paths' => [
                    __('Global: ~/.cursor/mcp.json'),
                    __('Project: .cursor/mcp.json in your repo root'),
                    __('Desktop UI: Settings → MCP → Add new global MCP server'),
                ],
                'menu_path' => __('Settings → MCP → Add server (Desktop) or ~/.cursor/mcp.json (CLI)'),
            ],
            'claude_desktop' => [
                'label' => __('Claude Desktop'),
                'icon' => 'lucide:monitor',
                'config_paths' => [
                    __('macOS: ~/Library/Application Support/Claude/claude_desktop_config.json'),
                    __('Windows: %APPDATA%\\Claude\\claude_desktop_config.json'),
                ],
                'menu_path' => __('Settings → Developer → Edit Config'),
            ],
            'claude_code' => [
                'label' => __('Claude Code'),
                'icon' => 'lucide:terminal',
                'config_paths' => [
                    __('Project root: .mcp.json'),
                    __('Global: ~/.claude.json'),
                ],
                'menu_path' => __('Add to your project .mcp.json or global Claude Code config'),
            ],
        ];
    }

    public function configJson(?string $token = null): string
    {
        return json_encode(
            $this->cursorConfigSnippet($token ?? 'YOUR_MCP_AGENT_TOKEN'),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @return array<int, array{name: string, description: string, permission: string|null, ability: string, group: string}>
     */
    public function availableTools(): array
    {
        return app(McpRegistryService::class)->toolDefinitions();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function groupedAvailableTools(): array
    {
        return app(McpRegistryService::class)->groupedToolDefinitions();
    }

    /**
     * @return array<string, mixed>
     */
    public function cursorConfigSnippet(?string $token = null, bool $useEnvVar = false): array
    {
        $config = [
            'mcpServers' => [
                'laradashboard' => [
                    'url' => $this->serverUrl(),
                ],
            ],
        ];

        if ($useEnvVar) {
            $config['mcpServers']['laradashboard']['headers'] = [
                'Authorization' => 'Bearer ${env:'.self::CURSOR_MCP_ENV_VAR.'}',
            ];

            return $config;
        }

        if ($token !== null && $token !== '') {
            $config['mcpServers']['laradashboard']['headers'] = [
                'Authorization' => 'Bearer '.$token,
            ];
        }

        return $config;
    }

    public function cursorEnvConfigJson(): string
    {
        return json_encode(
            $this->cursorConfigSnippet(useEnvVar: true),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function claudeDesktopConfigSnippet(?string $token = null): array
    {
        return $this->cursorConfigSnippet($token);
    }
}
