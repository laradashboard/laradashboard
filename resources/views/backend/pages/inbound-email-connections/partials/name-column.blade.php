<div class="flex items-center gap-2">
    <div class="flex-shrink-0">
        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <iconify-icon icon="lucide:mail-open" class="text-blue-600 dark:text-blue-400"></iconify-icon>
        </div>
    </div>
    <div>
        <p class="font-medium text-gray-900 dark:text-white">{{ $connection->name }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $connection->imap_folder }}</p>
    </div>
</div>
