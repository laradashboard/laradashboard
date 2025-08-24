@props(['limit' => 5])

<div class="space-y-4">
    @for ($i = 0; $i < $limit; $i++)
        <div class="flex gap-2 items-center justify-around py-2 animate-pulse">
            <!-- Avatar -->
            <div class="w-10 h-10 min-w-10 min-h-10 rounded-full bg-gray-200 dark:bg-gray-700 mr-10 ml-10"></div>
            <!-- Name & Username -->
            <div class="flex flex-col flex-1 min-w-0">
                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded mb-1"></div>
                <div class="h-3 w-16 bg-gray-100 dark:bg-gray-800 rounded"></div>
            </div>
            <!-- Email -->
            <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded mx-4"></div>
            <!-- Roles -->
            <div class="flex gap-1">
                <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <!-- Actions -->
            <div class="flex gap-2 ml-auto">
                <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
        </div>
    @endfor
</div>