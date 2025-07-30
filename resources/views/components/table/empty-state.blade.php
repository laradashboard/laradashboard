@props([
    'title' => __('No Datas found'),
    'description' => __('Get started by creating new data.'),
    'action' => null,
    'actionId' => null,
    'actionLabel' => __('Create'),
])

<div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex flex-col items-center justify-center text-center">

    <iconify-icon icon="bi:megaphone" class="text-gray-300 dark:text-gray-600 text-6xl mb-4"></iconify-icon>

    @if (!empty($title))
        <div class="font-semibold crm:text-lg text-gray-800 mb-1">
            {{ $title }}
        </div>
    @endif

    @if (!empty($description))
        <div class="text-gray-500 mb-4">
            {{ $description }}
        </div>
    @endif

    @if ($actionId)
        <button type="button"
            onclick="if(typeof openDrawer === 'function'){ openDrawer('{{ $actionId }}'); } else { window.dispatchEvent(new CustomEvent('open-drawer-{{ $actionId }}')); } return false;"
            class="btn btn-primary flex items-center">
            <iconify-icon icon="feather:plus" class="crm:mr-2 crm:mt-1"></iconify-icon>
            {{ $actionLabel }}
        </button>
    @elseif ($action)
        <a href="{{ $action }}" class="btn btn-primary flex items-center">
            <iconify-icon icon="feather:plus" class="crm:mr-2 crm:mt-1"></iconify-icon>
            {{ $actionLabel }}
        </a>
    @endif
</div>
