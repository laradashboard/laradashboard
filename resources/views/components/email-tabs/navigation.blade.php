@props(['currentTab' => 'email-settings'])

<div class="mb-6 border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
        <li class="me-2" role="presentation">
            <a href="{{ route('admin.email-settings.index') }}"
                class="flex items-center justify-center p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300
                {{ $currentTab === 'email-settings' ? 'text-primary border-primary dark:text-primary dark:border-primary' : 'text-gray-500 border-transparent dark:text-gray-400' }}"
                role="tab">
                <iconify-icon icon="lucide:settings" class="mr-2"></iconify-icon>
                {{ __('Email Settings') }}
            </a>
        </li>
        <li class="me-2" role="presentation">
            <a href="{{ route('admin.email-connections.index') }}"
                class="flex items-center justify-center p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300
                {{ $currentTab === 'connections' ? 'text-primary border-primary dark:text-primary dark:border-primary' : 'text-gray-500 border-transparent dark:text-gray-400' }}"
                role="tab">
                <iconify-icon icon="lucide:plug" class="mr-2"></iconify-icon>
                {{ __('Connections') }}
            </a>
        </li>
        <li class="me-2" role="presentation">
            <a href="{{ route('admin.email-templates.index') }}"
                class="flex items-center justify-center p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300
                {{ $currentTab === 'email-templates' ? 'text-primary border-primary dark:text-primary dark:border-primary' : 'text-gray-500 border-transparent dark:text-gray-400' }}"
                role="tab">
                <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                {{ __('Email Templates') }}
            </a>
        </li>
        <li class="me-2" role="presentation">
            <a href="{{ route('admin.notifications.index') }}"
                class="flex items-center justify-center p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300
                {{ $currentTab === 'notifications' ? 'text-primary border-primary dark:text-primary dark:border-primary' : 'text-gray-500 border-transparent dark:text-gray-400' }}"
                role="tab">
                <iconify-icon icon="lucide:bell" class="mr-2"></iconify-icon>
                {{ __('Notifications') }}
            </a>
        </li>
    </ul>
</div>
