<div>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="['title' => __('Users')]">
            <x-slot name="title_after">
                @if (request('role'))
                    <span class="badge">{{ ucfirst(request('role')) }}</span>
                @endif
            </x-slot>
        </x-breadcrumbs>

        {!! ld_apply_filters('users_after_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="table-td sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Users') }}</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->can('user.create'))
                        <a href="{{ route('admin.users.create') }}" class="btn-primary flex items-center gap-2">
                            <iconify-icon icon="feather:plus" height="16"></iconify-icon>
                            {{ __('New User') }}
                        </a>
                        @endif
                    </div>
                </div>

                <!-- PowerGrid Component -->
                <div class="p-4">
                    <livewire:power-grid:user-table />
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <script>
        window.addEventListener('show-message', event => {
            const { type, message } = event.detail;
            
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg ${
                type === 'success' 
                    ? 'bg-green-500 text-white' 
                    : 'bg-red-500 text-white'
            }`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    </script>
</div>