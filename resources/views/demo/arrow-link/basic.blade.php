<div class="flex flex-col space-y-4">
    <div class="space-y-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">Basic Link</h4>
        <x-arrow-link text="Continue Reading" href="/example" />
    </div>

    <div class="space-y-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">With Underline</h4>
        <x-arrow-link text="Underlined Link" href="#" :underline="true" />
    </div>

    <div class="space-y-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">Using Slot</h4>
        <x-arrow-link href="#">Learn More</x-arrow-link>
    </div>
</div>