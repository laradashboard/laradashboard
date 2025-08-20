@props([
    'placeholder' => 'Search...',
    'value' => request('search'),
])

<form method="GET" action="{{ request()->url() }}" class="flex-1 md:flex-none">
    @foreach(request()->except(['search', 'page']) as $key => $value)
        @if(is_array($value))
            @foreach($value as $arrayValue)
                <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    
    <div class="relative">
        <input
            type="text"
            name="search"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="form-control pl-10 pr-4 py-2 w-full md:w-64"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <iconify-icon icon="lucide:search" class="text-gray-400"></iconify-icon>
        </div>
        @if($value)
            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" 
               class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <iconify-icon icon="lucide:x" class="text-gray-400 hover:text-gray-600"></iconify-icon>
            </a>
        @endif
    </div>
</form>