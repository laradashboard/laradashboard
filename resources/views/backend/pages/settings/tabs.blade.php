<div class="mb-4 border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" data-tabs-toggle="#default-styled-tab-content"
        data-tabs-active-classes="text-primary hover:text-primary border-primary dark:border-primary"
        data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-300 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300"
        role="tablist">
        @php
           $activeTab = request('tab', 'general');
        @endphp
        @foreach ($tabs as $key => $tab)
            {!! ld_apply_filters('settings_tab_menu_before_' . $key, '') !!}
            <li class="me-2" role="presentation">
                <button
                    class="inline-block p-4 border-b-2 rounded-t-lg
               hover:text-gray-600 hover:border-gray-300
               dark:hover:text-gray-300 text-primary hover:text-primary
               {{ $activeTab == $key ? 'border-b-2 text-primary border-primary dark:text-primary dark:border-primary' : 'text-gray-500 border-transparent' }}"
                    id="{{ $key }}-tab" data-tabs-target="#{{ $key }}" type="button" role="tab" data-tab="{{ $key }}"
                    aria-controls="{{ $key }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                    {{ $tab['title'] }}
                </button>
            </li>
            {!! ld_apply_filters('settings_tab_menu_after_' . $key, '') !!}
        @endforeach
    </ul>
</div>
<div id="default-styled-tab-content">
    @foreach ($tabs as $key => $tab)
        {!! ld_apply_filters('settings_tab_content_before_' . $key, '') !!}
        <div class="hidden rounded-md dark:bg-gray-800 mb-3" id="{{ $key }}" role="tabpanel"
            aria-labelledby="{{ $key }}-tab">
            @if (isset($tab['view']))
                @include($tab['view'], $tab['data'] ?? [])
            @else
                {!! $tab['content'] !!}
            @endif
        </div>
        {!! ld_apply_filters('settings_tab_content_after_' . $key, '') !!}
    @endforeach
</div>
