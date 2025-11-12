<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.email-templates.show', $emailTemplate->uuid) }}" 
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
        <iconify-icon icon="feather:eye" class="w-3 h-3 mr-1"></iconify-icon>
        {{ __('View') }}
    </a>
    
    <button onclick="openTestEmailModal('{{ $emailTemplate->uuid }}')" 
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 bg-green-50 rounded hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50">
        <iconify-icon icon="feather:mail" class="w-3 h-3 mr-1"></iconify-icon>
        {{ __('Test') }}
    </button>
    
    <a href="{{ route('admin.email-templates.edit', $emailTemplate->id) }}" 
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 bg-gray-50 rounded hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
        <iconify-icon icon="feather:edit-2" class="w-3 h-3 mr-1"></iconify-icon>
        {{ __('Edit') }}
    </a>
</div>