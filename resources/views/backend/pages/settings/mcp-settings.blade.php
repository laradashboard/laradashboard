@php
    use App\Enums\Hooks\SettingFilterHook;
    use App\Models\Setting;
    use App\Services\Mcp\McpSettingsService;
    use App\Services\Mcp\McpTokenService;

    $mcpSettings = app(McpSettingsService::class);
    $mcpTokenService = app(McpTokenService::class);
    $mcpEnabled = $mcpSettings->isEnabled();
    $mcpServerUrl = $mcpSettings->serverUrl();
    $mcpTokens = auth()->user() ? $mcpTokenService->listTokensForUser(auth()->user()) : collect();
    $availableTools = $mcpSettings->availableTools();
    $groupedTools = $mcpSettings->groupedAvailableTools();
    $placeholderConfig = $mcpSettings->configJson();
    $defaultClient = $mcpSettings->defaultAiClient();
    $csrfToken = csrf_token();
    $createTokenUrl = route('admin.settings.mcp.tokens.store');
    $revokeTokenUrlTemplate = route('admin.settings.mcp.tokens.destroy', ['tokenId' => '__TOKEN_ID__']);
@endphp

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_MCP_TAB_BEFORE_SECTION_START, '') !!}

<x-card>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:bot" width="20" height="20" class="text-primary"></iconify-icon>
            {{ __('Model Context Protocol (MCP)') }}
        </div>
    </x-slot>

    <div class="space-y-6" x-data="mcpSettingsPanel({
        createTokenUrl: @js($createTokenUrl),
        revokeTokenUrlTemplate: @js($revokeTokenUrlTemplate),
        csrfToken: @js($csrfToken),
        serverUrl: @js($mcpServerUrl),
        selectedClient: @js($defaultClient),
        groupedTools: @js($groupedTools),
        mcpEnabledSaved: @js($mcpEnabled),
        demoMode: @js(config('app.demo_mode', false)),
    })">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('Connect Cursor, Claude Desktop, or Claude Code to manage content and settings from your AI client.') }}
        </p>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <input type="hidden" name="mcp_enabled" value="0">
                    <div class="shrink-0">
                        <x-inputs.toggle
                            name="mcp_enabled"
                            :checked="$mcpEnabled"
                            :disabled="config('app.demo_mode', false)"
                        />
                    </div>
                    <label for="mcp_enabled" class="font-medium text-gray-900 dark:text-white text-sm">
                        {{ __('Enable MCP server') }}
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                    <span
                        x-show="canUseMcp()"
                        x-cloak
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300"
                    >
                        <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        {{ __('Enabled') }}
                    </span>
                    <span
                        x-show="!canUseMcp() && mcpPendingSave()"
                        x-cloak
                        class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300"
                    >
                        <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                        {{ __('Unsaved changes') }}
                    </span>
                    <span
                        x-show="!canUseMcp() && !mcpPendingSave()"
                        x-cloak
                        class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        <span class="h-2 w-2 rounded-full bg-gray-400" aria-hidden="true"></span>
                        {{ __('Disabled') }}
                    </span>
                    <x-buttons.submit-buttons
                        :submit-label="__('Save Changes')"
                        :class-names="['wrapper' => 'flex gap-3']"
                    />
                </div>
            </div>

            <div class="border-t border-gray-200 p-5 dark:border-gray-700">
                <label class="form-label" for="mcp-server-url">{{ __('MCP server URL') }}</label>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ $mcpServerUrl }}" class="form-control font-mono text-sm" id="mcp-server-url">
                    <x-copy-button
                        :copy-value="$mcpServerUrl"
                        class="btn-outline-primary shrink-0"
                    />
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Agent tokens') }}</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use a dedicated MCP token — not your login credentials.') }}
                    </p>
                </div>
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openTokenModal()"
                    :disabled="!canUseMcp()"
                    :class="{ 'opacity-50 cursor-not-allowed': !canUseMcp() }"
                >
                    {{ __('Generate MCP token') }}
                </button>
            </div>

            <div
                x-show="tokenModalOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                aria-labelledby="mcp-token-modal-title"
                @keydown.escape.window="closeTokenModal()"
            >
                <div
                    @click.outside="closeTokenModal()"
                    class="relative mx-4 w-full max-w-lg rounded-lg bg-white shadow-lg dark:bg-gray-900"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 id="mcp-token-modal-title" class="font-semibold text-gray-900 dark:text-white">
                            {{ __('Name your MCP token') }}
                        </h3>
                        <button
                            type="button"
                            class="btn-outline-secondary flex h-6 w-6 items-center justify-center p-1"
                            @click="closeTokenModal()"
                            :aria-label="@js(__('Close'))"
                        >
                            <iconify-icon icon="mdi:close" class="flex h-5 w-5" aria-hidden="true"></iconify-icon>
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Give this token a descriptive name so you can remember where it is used — for example, "Cursor SEO blog" or "Claude Desktop local dev".') }}
                        </p>

                        <div>
                            <label for="mcp-token-label" class="form-label">{{ __('Token name') }}</label>
                            <input
                                id="mcp-token-label"
                                type="text"
                                x-ref="tokenLabelInput"
                                x-model="tokenLabel"
                                class="form-control"
                                maxlength="100"
                                placeholder="{{ __('e.g. Cursor blog automation') }}"
                                @keydown.enter.prevent="createToken()"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Required. Shown in your token list; not sent to connected agents.') }}
                            </p>
                            <p x-show="tokenLabelError" x-cloak class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="tokenLabelError"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <button type="button" class="btn btn-outline-secondary" @click="closeTokenModal()" :disabled="creatingToken">
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="createToken()"
                            :disabled="creatingToken"
                            :class="{ 'opacity-50 cursor-not-allowed': creatingToken }"
                        >
                            <span x-show="!creatingToken">{{ __('Generate token') }}</span>
                            <span x-show="creatingToken">{{ __('Generating...') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <template x-if="newToken">
                <div class="mb-4 p-4 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                    <p class="text-sm font-medium text-amber-900 dark:text-amber-200 mb-2">
                        {{ __('Copy this token now. It will not be shown again.') }}
                    </p>
                    <div class="flex gap-2">
                        <input type="text" readonly :value="newToken" class="form-control font-mono text-xs">
                        <x-copy-button
                            class="btn-outline-primary"
                            x-bind:data-copy-value="newToken"
                        />
                    </div>
                </div>
            </template>

            <template x-if="tokenError">
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 text-sm" x-text="tokenError"></div>
            </template>

            <p id="mcp-tokens-empty" class="text-sm text-gray-500 dark:text-gray-400 @if(!$mcpTokens->isEmpty()) hidden @endif">
                {{ __('No MCP agent tokens yet.') }}
            </p>

            <div class="overflow-x-auto @if($mcpTokens->isEmpty()) hidden @endif" id="mcp-tokens-table-wrap">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4">{{ __('Name') }}</th>
                            <th class="py-2 pr-4">{{ __('Created') }}</th>
                            <th class="py-2 pr-4">{{ __('Last used') }}</th>
                            <th class="py-2 pr-4">{{ __('Abilities') }}</th>
                            <th class="py-2">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="mcp-tokens-body">
                        @foreach ($mcpTokens as $token)
                            <tr class="border-b border-gray-100 dark:border-gray-800" id="mcp-token-row-{{ $token->id }}">
                                <td class="py-3 pr-4 font-medium text-gray-900 dark:text-white">
                                    {{ $mcpTokenService->tokenLabel($token) }}
                                </td>
                                <td class="py-3 pr-4">{{ $token->created_at?->format('M j, Y g:i A') }}</td>
                                <td class="py-3 pr-4">{{ $token->last_used_at?->diffForHumans() ?? __('Never') }}</td>
                                <td class="py-3 pr-4">
                                    <code class="text-xs">{{ implode(', ', $token->abilities ?? []) }}</code>
                                </td>
                                <td class="py-3">
                                    <button type="button"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm"
                                        @click="revokeToken({{ $token->id }})">
                                        {{ __('Revoke') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div :class="{ 'opacity-60': !canUseMcp() }">
            @include('backend.pages.settings.partials.mcp-connection-guide')
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-4">
                <div class="min-w-0 flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('Available tools') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Tools are registered automatically. Modules can add MCP tools via the mcp.tools hook.') }}
                    </p>
                </div>

                @if (count($availableTools) > 0)
                    <div class="w-full sm:w-72 shrink-0">
                        <label for="mcp-tool-search" class="sr-only">{{ __('Search MCP tools') }}</label>
                        <div class="relative">
                            <iconify-icon
                                icon="lucide:search"
                                width="16"
                                height="16"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                                aria-hidden="true"
                            ></iconify-icon>
                            <input
                                id="mcp-tool-search"
                                type="search"
                                x-model="toolSearch"
                                placeholder="{{ __('Search tools...') }}"
                                class="form-control pl-9 text-sm"
                                autocomplete="off"
                            >
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 text-right" aria-live="polite">
                            <span x-show="! toolSearch.trim()">{{ trans_choice(':count tool|:count tools', count($availableTools), ['count' => count($availableTools)]) }}</span>
                            <span x-show="toolSearch.trim()" x-cloak x-text="filteredToolCountLabel()"></span>
                        </p>
                    </div>
                @else
                    <span class="inline-flex items-center self-start px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 shrink-0">
                        {{ trans_choice(':count tool|:count tools', 0, ['count' => 0]) }}
                    </span>
                @endif
            </div>

            <template x-if="Object.keys(groupedTools).length === 0">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No MCP tools are registered yet.') }}</p>
            </template>

            <template x-if="Object.keys(groupedTools).length > 0 && visibleToolCount() === 0">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No tools match your search.') }}</p>
            </template>

            <div class="space-y-6">
                <template x-for="[group, tools] in Object.entries(groupedTools)" :key="group">
                    <div x-show="groupHasVisibleTools(group, tools)">
                        <h5 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" x-text="group"></h5>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <template x-for="tool in tools" :key="tool.name">
                                <div
                                    x-show="matchesToolSearch(tool, group)"
                                    class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <code class="text-sm font-semibold text-primary" x-text="tool.name"></code>
                                        <span class="shrink-0 px-2 py-0.5 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300" x-text="tool.ability"></span>
                                    </div>
                                    <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300" x-text="tool.description"></p>
                                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Permission') }}:
                                        <span class="font-medium text-gray-700 dark:text-gray-300" x-text="tool.permission || @js(__('none'))"></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_MCP_TAB_AFTER_SECTION_END, '') !!}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mcpSettingsPanel', ({ createTokenUrl, revokeTokenUrlTemplate, csrfToken, serverUrl, selectedClient, groupedTools, mcpEnabledSaved, demoMode }) => ({
        creatingToken: false,
        tokenModalOpen: false,
        tokenLabel: '',
        tokenLabelError: null,
        newToken: null,
        tokenError: null,
        selectedClient: selectedClient || @js($defaultClient),
        configSnippet: @js($placeholderConfig),
        serverUrl,
        groupedTools: groupedTools || {},
        toolSearch: '',
        mcpEnabledSaved: Boolean(mcpEnabledSaved),
        mcpToggleOn: Boolean(mcpEnabledSaved),
        demoMode: Boolean(demoMode),

        init() {
            const toggle = document.getElementById('mcp_enabled');

            if (toggle) {
                this.mcpToggleOn = toggle.checked;
                toggle.addEventListener('change', () => {
                    this.mcpToggleOn = toggle.checked;
                });
            }
        },

        canUseMcp() {
            return this.mcpEnabledSaved && !this.demoMode;
        },

        mcpPendingSave() {
            return this.mcpToggleOn && !this.mcpEnabledSaved;
        },

        matchesToolSearch(tool, group) {
            const query = this.toolSearch.trim().toLowerCase();

            if (! query) {
                return true;
            }

            const haystack = [
                group,
                tool.name,
                tool.description,
                tool.ability,
                tool.permission || '',
            ].join(' ').toLowerCase();

            return haystack.includes(query);
        },

        groupHasVisibleTools(group, tools) {
            return tools.some((tool) => this.matchesToolSearch(tool, group));
        },

        visibleToolCount() {
            return Object.entries(this.groupedTools).reduce((count, [group, tools]) => {
                return count + tools.filter((tool) => this.matchesToolSearch(tool, group)).length;
            }, 0);
        },

        filteredToolCountLabel() {
            const count = this.visibleToolCount();
            const template = count === 1 ? @js(__(':count tool')) : @js(__(':count tools'));

            return template.replace(':count', count);
        },

        updateConfigSnippet(token) {
            this.configSnippet = JSON.stringify({
                mcpServers: {
                    laradashboard: {
                        url: this.serverUrl,
                        headers: {
                            Authorization: 'Bearer ' + token,
                        },
                    },
                },
            }, null, 2);
        },

        openTokenModal() {
            if (!this.canUseMcp()) {
                if (this.demoMode) {
                    this.tokenError = @js(__('MCP is unavailable in demo mode.'));
                } else if (this.mcpPendingSave()) {
                    this.tokenError = @js(__('Save changes to activate MCP.'));
                } else {
                    this.tokenError = @js(__('Enable MCP and save to connect agents.'));
                }

                return;
            }

            this.tokenLabel = '';
            this.tokenLabelError = null;
            this.tokenModalOpen = true;

            this.$nextTick(() => {
                this.$refs.tokenLabelInput?.focus();
            });
        },

        closeTokenModal(force = false) {
            if (this.creatingToken && ! force) {
                return;
            }

            this.tokenModalOpen = false;
            this.tokenLabel = '';
            this.tokenLabelError = null;
        },

        async createToken() {
            if (!this.canUseMcp()) {
                this.closeTokenModal();
                return;
            }

            const label = this.tokenLabel.trim();

            if (label.length < 2) {
                this.tokenLabelError = @js(__('Please enter a name for this MCP token.'));
                return;
            }

            this.creatingToken = true;
            this.tokenError = null;
            this.tokenLabelError = null;

            try {
                const response = await fetch(createTokenUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ label }),
                });

                const data = await response.json();

                if (!response.ok) {
                    const validationMessage = data.errors?.label?.[0];
                    this.tokenLabelError = validationMessage || data.message || @js(__('Failed to create token.'));
                    return;
                }

                this.newToken = data.token;
                this.updateConfigSnippet(data.token);
                this.appendTokenRow(data.token_record);
                this.creatingToken = false;
                this.closeTokenModal(true);
            } catch (error) {
                this.tokenLabelError = error.message;
            } finally {
                this.creatingToken = false;
            }
        },

        appendTokenRow(record) {
            if (!record) {
                return;
            }

            document.getElementById('mcp-tokens-empty')?.classList.add('hidden');
            document.getElementById('mcp-tokens-table-wrap')?.classList.remove('hidden');

            const tbody = document.getElementById('mcp-tokens-body');

            if (!tbody) {
                return;
            }

            const row = document.createElement('tr');
            row.id = 'mcp-token-row-' + record.id;
            row.className = 'border-b border-gray-100 dark:border-gray-800';
            row.innerHTML = `
                <td class="py-3 pr-4 font-medium text-gray-900 dark:text-white"></td>
                <td class="py-3 pr-4"></td>
                <td class="py-3 pr-4"></td>
                <td class="py-3 pr-4"><code class="text-xs"></code></td>
                <td class="py-3"></td>
            `;

            const cells = row.querySelectorAll('td');
            cells[0].textContent = record.label;
            cells[1].textContent = record.created_at;
            cells[2].textContent = record.last_used_at;
            cells[3].querySelector('code').textContent = record.abilities;
            cells[4].innerHTML = `<button type="button" class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm" data-revoke-token="${record.id}">${@js(__('Revoke'))}</button>`;
            cells[4].querySelector('button')?.addEventListener('click', () => this.revokeToken(record.id));

            tbody.prepend(row);
        },

        async revokeToken(tokenId) {
            if (!confirm(@js(__('Revoke this MCP token? Connected agents will stop working immediately.')))) {
                return;
            }

            const url = revokeTokenUrlTemplate.replace('__TOKEN_ID__', tokenId);

            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                document.getElementById('mcp-token-row-' + tokenId)?.remove();

                if (!document.querySelector('#mcp-tokens-body tr')) {
                    document.getElementById('mcp-tokens-empty')?.classList.remove('hidden');
                    document.getElementById('mcp-tokens-table-wrap')?.classList.add('hidden');
                }
            }
        },
    }));
});
</script>
@endpush
