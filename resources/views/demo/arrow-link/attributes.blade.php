<div class="flex flex-col space-y-4">
    <div class="space-y-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">With Underline</h4>
        <x-arrow-link text="Underlined Link" href="#" color="blue" :underline="true" />
    </div>

    <div class="space-y-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">External Link</h4>
        <x-arrow-link text="Visit Site" href="https://example.com" target="_blank" color="green" />
    </div>
</div>