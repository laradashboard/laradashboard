@php
    use App\Services\Mcp\McpSettingsService;
    use App\Services\MediaLibraryService;
    use App\Support\Helper\MediaHelper;

    $clients = $mcpSettings->supportedClients();
    $defaultClient = $mcpSettings->defaultAiClient();
    $isInsecureHttp = $mcpSettings->isInsecureHttp();
    $cursorMcpEnvVar = McpSettingsService::CURSOR_MCP_ENV_VAR;
    $multipartUploadUrl = $mcpSettings->multipartUploadUrl();
    $base64FallbackMax = MediaHelper::formatFileSize(MediaLibraryService::MCP_BASE64_FALLBACK_MAX_BYTES);
    $multipartMax = MediaHelper::formatFileSize(MediaLibraryService::MCP_MAX_UPLOAD_BYTES);
@endphp

<div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-6">
    <div>
        <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Connect Your AI Client') }}</h4>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Follow the steps below to connect Cursor (Desktop or CLI), Claude Desktop, or Claude Code to this LaraDashboard site.') }}
        </p>
    </div>

    <div class="max-w-md">
        <label for="mcp_ai_client" class="form-label">{{ __('AI Client') }}</label>
        <select id="mcp_ai_client" class="form-control" x-model="selectedClient" :disabled="!canUseMcp()">
            @foreach ($clients as $clientId => $client)
                <option value="{{ $clientId }}">{{ $client['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-8">
        {{-- Step 1 --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">1</div>
            <div class="flex-1 min-w-0 space-y-2">
                <h5 class="font-medium text-gray-900 dark:text-white">{{ __('Enable MCP and save settings') }}</h5>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Turn on Enable MCP server above, then click Save Changes. The MCP endpoint stays hidden (404) until this is enabled.') }}
                </p>
                <p class="text-sm text-amber-700 dark:text-amber-300" x-show="!canUseMcp() && !mcpPendingSave() && !demoMode">
                    {{ __('MCP is currently disabled. Complete this step before generating a token or connecting a client.') }}
                </p>
                <p class="text-sm text-amber-700 dark:text-amber-300" x-show="mcpPendingSave()" x-cloak>
                    {{ __('You turned on MCP but have not saved yet. Save changes to continue.') }}
                </p>
            </div>
        </div>

        {{-- Step 2 --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">2</div>
            <div class="flex-1 min-w-0 space-y-2">
                <h5 class="font-medium text-gray-900 dark:text-white">{{ __('Generate an MCP agent token') }}</h5>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Use the Generate MCP token button above. Copy the token immediately — it is shown only once. Regular API login tokens will not work.') }}
                </p>
                <template x-if="newToken">
                    <p class="text-sm text-emerald-700 dark:text-emerald-300">
                        {{ __('Token generated. Use it in the config below.') }}
                    </p>
                </template>
            </div>
        </div>

        {{-- Step 3 - client specific --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">3</div>
            <div class="flex-1 min-w-0 space-y-3">
                <h5 class="font-medium text-gray-900 dark:text-white">
                    <span x-show="selectedClient === 'cursor'">{{ __('Add the config in Cursor (Desktop or CLI)') }}</span>
                    <span x-show="selectedClient === 'claude_desktop'">{{ __('Add the config to Claude Desktop') }}</span>
                    <span x-show="selectedClient === 'claude_code'">{{ __('Add the config for Claude Code') }}</span>
                </h5>

                <template x-if="selectedClient === 'cursor'">
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <p>{{ __('Cursor Desktop and Cursor CLI use the same MCP config file. Merge the JSON below under mcpServers.') }}</p>

                        <div class="space-y-2">
                            <p class="font-medium text-gray-900 dark:text-white">{{ __('Cursor Desktop') }}</p>
                            <p>{{ __('Open Cursor → Settings → MCP → Add new global MCP server, or edit ~/.cursor/mcp.json directly.') }}</p>
                        </div>

                        <div class="space-y-2">
                            <p class="font-medium text-gray-900 dark:text-white">{{ __('Cursor CLI') }}</p>
                            <p>{{ __('Edit ~/.cursor/mcp.json for global access, or .cursor/mcp.json in your project root for repo-specific setup. The CLI reads the same file as the desktop app.') }}</p>
                            <ul class="list-disc list-inside space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                <li>{{ __('Global: ~/.cursor/mcp.json') }}</li>
                                <li>{{ __('Project: .cursor/mcp.json') }}</li>
                            </ul>
                        </div>
                    </div>
                </template>

                <template x-if="selectedClient === 'claude_desktop'">
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <p>{{ __('Open Claude Desktop → Settings → Developer → Edit Config.') }}</p>
                        <p>{{ __('Paste the JSON below into claude_desktop_config.json and save the file.') }}</p>
                        <ul class="list-disc list-inside space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <li>{{ __('macOS: ~/Library/Application Support/Claude/claude_desktop_config.json') }}</li>
                            <li>{{ __('Windows: %APPDATA%\\Claude\\claude_desktop_config.json') }}</li>
                        </ul>
                    </div>
                </template>

                <template x-if="selectedClient === 'claude_code'">
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <p>{{ __('Create or edit .mcp.json in your project root, or add the server block to ~/.claude.json for global access.') }}</p>
                        <p>{{ __('Paste the JSON below, then restart Claude Code so it picks up the new server.') }}</p>
                    </div>
                </template>

                <div class="relative overflow-hidden rounded-xl bg-gray-900">
                    <div class="absolute top-2 right-2 z-10">
                        <x-copy-button
                            :label="__('Copy JSON')"
                            class="btn-sm !bg-gray-800 !text-gray-200 !border-gray-600 hover:!bg-gray-700"
                            x-bind:data-copy-value="configSnippet"
                            x-bind:disabled="!canUseMcp()"
                            x-bind:class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !canUseMcp() }"
                        />
                    </div>
                    <pre class="p-4 pt-12 text-xs text-gray-100 overflow-x-auto"><code x-text="configSnippet">{{ $placeholderConfig }}</code></pre>
                </div>
            </div>
        </div>

        {{-- Step 4 --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">4</div>
            <div class="flex-1 min-w-0 space-y-2">
                <h5 class="font-medium text-gray-900 dark:text-white">{{ __('Replace the token placeholder') }}</h5>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Replace YOUR_MCP_AGENT_TOKEN in the Authorization header with the token you generated in step 2.') }}
                </p>
                <template x-if="selectedClient === 'cursor'">
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('For Cursor CLI, you can keep the token out of the config file using an environment variable:') }}
                        </p>
                        <div class="relative overflow-hidden rounded-lg bg-gray-900">
                            <pre class="p-3 text-xs text-gray-100 overflow-x-auto"><code>{{ $mcpSettings->cursorEnvConfigJson() }}</code></pre>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Then run:') }}
                            <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-mono">export {{ $cursorMcpEnvVar }}=your-token</code>
                        </p>
                    </div>
                </template>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Server URL:') }}
                    <code class="px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 rounded font-mono">{{ $mcpServerUrl }}</code>
                </p>
            </div>
        </div>

        {{-- Step 5 --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">5</div>
            <div class="flex-1 min-w-0 space-y-2">
                <h5 class="font-medium text-gray-900 dark:text-white">{{ __('Restart your AI client and verify') }}</h5>

                <template x-if="selectedClient === 'cursor'">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Cursor Desktop') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ __('Restart Cursor so it reloads MCP config, then confirm laradashboard appears under Settings → MCP.') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Cursor CLI') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ __('From your terminal, verify the server is connected:') }}
                            </p>
                            <ul class="space-y-1.5 text-xs font-mono text-gray-600 dark:text-gray-300">
                                <li><code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">agent mcp enable laradashboard</code></li>
                                <li><code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">agent mcp list</code></li>
                                <li><code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">agent mcp list-tools laradashboard</code></li>
                            </ul>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ __('Start a session with agent, or run a one-off prompt:') }}
                            </p>
                            <code class="block text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1.5 rounded font-mono text-gray-700 dark:text-gray-300">agent -p "List the 5 most recent posts using laradashboard MCP"</code>
                        </div>
                    </div>
                </template>

                <template x-if="selectedClient !== 'cursor'">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Restart the AI client so it reloads MCP config, then ask it to list posts or create a draft blog post.') }}
                    </p>
                </template>

                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ __('List the 5 most recent published posts') }}</code></li>
                    <li><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ __('What is on my daily briefing?') }}</code></li>
                    <li><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ __('Create a blog post about getting started with LaraDashboard') }}</code></li>
                </ul>
            </div>
        </div>

        {{-- Step 6 — hero images --}}
        <div class="flex gap-4">
            <div class="flex shrink-0 justify-center items-center w-8 h-8 text-sm font-semibold text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">6</div>
            <div class="flex-1 min-w-0 space-y-3">
                <h5 class="font-medium text-gray-900 dark:text-white">{{ __('Upload blog hero images (sharp, large files)') }}</h5>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Do not send large images as base64 through MCP tool calls — many clients truncate JSON payloads and uploads fail or look blurry. Use multipart upload instead. The agent token needs the mcp:media.write ability.') }}
                </p>

                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300 list-disc list-inside">
                    <li>
                        <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono">upload-media</code>
                        {{ __('— tiny assets only (about :size decoded or less).', ['size' => $base64FallbackMax]) }}
                    </li>
                    <li>
                        <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono">create-media-upload</code>
                        {{ __('→ POST the file → :tool — preferred for 1200×630 heroes up to :max.', [
                            'tool' => 'finalize-media-upload',
                            'max' => $multipartMax,
                        ]) }}
                    </li>
                    <li>
                        <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono">attach-featured-image</code>
                        {{ __('— after upload reports :field; the public URL must be serveable or attach fails.', ['field' => 'serve_ok: true']) }}
                    </li>
                </ul>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Agent workflow') }}</p>
                    <ol class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300 list-decimal list-inside">
                        <li>{{ __('Call create-media-upload with filename and mime_type (e.g. hero.jpg, image/jpeg).') }}</li>
                        <li>{{ __('POST multipart/form-data with field file (optional title, alt_text) to upload_url. The signed URL does not need a Bearer token. Or POST to upload_url_bearer with the same Authorization header as MCP.') }}</li>
                        <li>{{ __('Call finalize-media-upload with upload_token and confirm serve_ok is true in the response.') }}</li>
                        <li>{{ __('Call attach-featured-image with post_id and media_id.') }}</li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Multipart upload URL (Bearer token)') }}</p>
                    <code class="block text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1.5 rounded font-mono text-gray-700 dark:text-gray-300 break-all">{{ $multipartUploadUrl }}</code>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Example (replace paths and token):') }}
                    </p>
                    <div class="relative overflow-hidden rounded-lg bg-gray-900">
                        <pre class="p-3 text-xs text-gray-100 overflow-x-auto"><code>curl -X POST "{{ $multipartUploadUrl }}" \
  -H "Authorization: Bearer YOUR_MCP_AGENT_TOKEN" \
  -F "file=@/path/to/hero.jpg;type=image/jpeg" \
  -F "upload_token=UPLOAD_TOKEN_FROM_create-media-upload"</code></pre>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('The signed upload_url does not need an Authorization header. upload_token is required when using upload_url_bearer.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if ($isInsecureHttp)
        <div class="flex gap-3 p-4 text-sm rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 text-amber-900 dark:text-amber-200">
            <iconify-icon icon="lucide:triangle-alert" class="shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" width="18" height="18" aria-hidden="true"></iconify-icon>
            <p>
                {{ __('This site is running over HTTP. MCP will work for local development, but use HTTPS in production so agent tokens are encrypted in transit.') }}
            </p>
        </div>
    @endif
</div>
