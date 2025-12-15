@php
$text = $text ?? '';
$href = $href ?? '#';
$color = $color ?? 'blue';
$underline = $underline ?? false;
$underlineClass = $underline ? 'hover:underline' : 'no-underline';
@endphp

<a href="{{ $href }}" class="group inline-flex items-center gap-1.5 text-primary hover:opacity-90 {{ $underlineClass }} transition-colors">
    <span>{{ $text ?: $slot }}</span>
    <iconify-icon icon="lucide:arrow-right" class="text-sm group-hover:translate-x-0.5 transition-transform" width="16" height="16"></iconify-icon>
</a>