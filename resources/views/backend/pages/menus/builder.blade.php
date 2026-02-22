<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Menu Settings Panel --}}
        <div class="lg:col-span-1">
            <form action="{{ route('admin.menus.update', $menu->id) }}" method="POST" data-prevent-unsaved-changes>
                @csrf
                @method('PUT')

                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                                <iconify-icon icon="lucide:settings" class="text-xl"></iconify-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Menu Settings') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-normal">{{ __('Configure menu properties') }}</p>
                            </div>
                        </div>
                    </x-slot>

                    <div class="space-y-4">
                        <x-inputs.input
                            name="name"
                            id="name"
                            :label="__('Menu Name')"
                            :value="old('name', $menu->name)"
                            required
                        />

                        <x-inputs.select
                            name="location"
                            id="location"
                            :label="__('Location')"
                            :options="$locations"
                            :value="old('location', $menu->location)"
                            required
                        />

                        <x-inputs.textarea
                            name="description"
                            id="description"
                            :label="__('Description')"
                            :value="old('description', $menu->description)"
                            :rows="2"
                        />

                        <div>
                            <label class="form-label">{{ __('Status') }}</label>
                            <input type="hidden" name="status" value="inactive">
                            <x-inputs.toggle
                                name="status"
                                :label="__('Active')"
                                :checked="old('status', $menu->status) === 'active'"
                                value="active"
                            />
                        </div>
                    </div>

                    <x-slot name="footer">
                        <button type="submit" class="btn btn-primary w-full">
                            <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon>
                            {{ __('Save Settings') }}
                        </button>
                    </x-slot>
                </x-card>
            </form>
        </div>

        {{-- Menu Items Builder --}}
        <div class="lg:col-span-2">
            @livewire('admin.menus.builder', ['menu' => $menu])
        </div>
    </div>
</x-layouts.backend-layout>
