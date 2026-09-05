<div>
    @if(count($visibleCards) > 0)
        <div class="grid grid-cols-2 gap-4 md:grid-cols-5 lg:grid-cols-5 md:gap-6">
            {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_BEFORE_USERS, '') !!}

            @foreach($visibleCards as $card)
                <div class="group relative" wire:key="dashboard-stat-{{ $card['id'] }}">
                    @include('backend.pages.dashboard.partials.card', array_merge([
                        'class' => 'bg-white',
                        'enable_full_div_click' => true,
                        'stat_id' => $card['id'],
                        'hideable' => true,
                    ], $card))

                    @if(! empty($card['id']))
                        <button
                            type="button"
                            wire:click.stop="hideWidget('{{ $card['id'] }}')"
                            class="absolute right-3 top-3 z-10 inline-flex h-5 w-5 items-center justify-center rounded text-gray-400 opacity-0 transition-opacity hover:bg-gray-100 hover:text-gray-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 group-hover:opacity-100 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            title="{{ __('Hide widget') }}"
                            aria-label="{{ __('Hide :label widget', ['label' => $card['label']]) }}"
                        >
                            <iconify-icon icon="heroicons:minus-small" width="16" height="16" aria-hidden="true"></iconify-icon>
                        </button>
                    @endif
                </div>
            @endforeach

            {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_TRANSLATIONS, '') !!}
        </div>
    @endif
</div>
