{{-- Core frontend admin toolbar — uses inline styles to work with any theme's Tailwind prefix --}}
<div style="position:fixed;bottom:0;left:0;right:0;z-index:50;background:#111827;color:#fff;font-size:0.875rem;box-shadow:0 -2px 10px rgba(0,0,0,0.15);"
     x-data="{ open: true }"
     x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0 1rem;height:2.5rem;">
        {{-- Left side --}}
        <div style="display:flex;align-items:center;gap:0.75rem;">
            @foreach($leftItems as $index => $item)
                @if($index > 0)
                    <span style="color:#4b5563;font-size:0.75rem;">|</span>
                @endif
                <a href="{{ $item['url'] }}"
                   style="display:flex;align-items:center;gap:0.375rem;color:#d1d5db;text-decoration:none;transition:color 0.15s;"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#d1d5db'"
                   aria-label="{{ $item['label'] }}">
                    @if(!empty($item['icon']))
                        <iconify-icon icon="{{ $item['icon'] }}" style="font-size:0.875rem;" aria-hidden="true"></iconify-icon>
                    @endif
                    <span class="ld-toolbar-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Right side --}}
        <div style="display:flex;align-items:center;gap:0.75rem;">
            @foreach($rightItems as $item)
                <a href="{{ $item['url'] }}"
                   style="display:flex;align-items:center;gap:0.375rem;color:#d1d5db;text-decoration:none;transition:color 0.15s;"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#d1d5db'"
                   aria-label="{{ $item['label'] }}">
                    @if(!empty($item['icon']))
                        <iconify-icon icon="{{ $item['icon'] }}" style="font-size:0.875rem;" aria-hidden="true"></iconify-icon>
                    @endif
                    <span class="ld-toolbar-label">{{ $item['label'] }}</span>
                </a>
            @endforeach

            <span class="ld-toolbar-username" style="color:#9ca3af;">
                {{ auth()->user()->first_name ?? auth()->user()->name }}
            </span>

            <button @click="open = false" type="button"
                    style="padding:0.25rem;color:#9ca3af;background:none;border:none;cursor:pointer;transition:color 0.15s;"
                    onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#9ca3af'"
                    aria-label="{{ __('Close toolbar') }}">
                <iconify-icon icon="lucide:x" style="font-size:0.875rem;" aria-hidden="true"></iconify-icon>
            </button>
        </div>
    </div>
</div>

{{-- Responsive: hide labels on small screens, hide username below md --}}
<style>
    @media (max-width: 639px) {
        .ld-toolbar-label { display: none; }
    }
    @media (min-width: 640px) {
        .ld-toolbar-label { display: inline; }
    }
    @media (max-width: 767px) {
        .ld-toolbar-username { display: none; }
    }
    @media (min-width: 768px) {
        .ld-toolbar-username { display: inline; }
    }
</style>
