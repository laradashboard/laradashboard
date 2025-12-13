<!-- Duplicate Template Modal -->
<div id="duplicate-modal" class="crm:hidden crm:fixed crm:inset-0 crm:bg-gray-600 crm:bg-opacity-50 crm:overflow-y-auto crm:h-full crm:w-full crm:flex crm:items-center crm:justify-center crm:z-50">
    <div class="crm:relative crm:mx-auto crm:p-5 crm:border crm:w-96 crm:shadow-lg crm:rounded-md crm:bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="crm:mt-3 crm:text-center">
            <h3 class="crm:text-lg crm:leading-6 crm:font-medium crm:text-gray-900 dark:text-white">{{ __('Duplicate Template') }}</h3>
            <div class="crm:mt-2 crm:px-7 crm:py-3">
                
                <form id="duplicate-form" action="" method="POST" class="crm:mt-4 crm:text-left">
                    @csrf
                    <div class="crm:mb-4">
                        <label for="duplicate-name" class="crm:block crm:text-sm crm:font-medium crm:text-gray-700 dark:text-gray-300 crm:mb-1">
                            {{ __('Template Name') }} <span class="crm:text-red-500">*</span>
                        </label>
                        <input type="text" id="duplicate-name" name="name" 
                            class="crm:block crm:w-full crm:px-3 crm:py-2 crm:border crm:border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white crm:rounded-md crm:shadow-sm crm:focus:outline-none crm:focus:ring-indigo-500 crm:focus:border-indigo-500 crm:sm:text-sm"
                            required>
                    </div>
                    
                    <div class="crm:flex crm:items-center crm:justify-between crm:mt-6">
                        <button type="button" onclick="closeDuplicate()" 
                            class="crm:inline-flex crm:justify-center crm:px-4 crm:py-2 crm:text-sm crm:font-medium crm:text-gray-700 crm:bg-white crm:border crm:border-gray-300 crm:rounded-md crm:shadow-sm hover:crm:bg-gray-50 crm:focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('Cancel') }}
                        </button>
                        <x-buttons.submit-buttons  />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
