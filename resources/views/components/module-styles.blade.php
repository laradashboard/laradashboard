{{-- Output is resolved in App\View\Components\ModuleStyles: emit the built Vite
     tags when available, else a debug-only hint, else nothing — so an unbuilt
     module bundle never fatals the page. --}}
@if ($tags !== null)
{{ $tags }}
@elseif ($hint !== null)
{!! $hint !!}
@endif
