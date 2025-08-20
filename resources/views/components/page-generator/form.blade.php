@props([
    'formAction',
    'formMethod' => 'POST',
    'formId' => 'form',
    'formClasses' => '',
    'enctype' => 'application/x-www-form-urlencoded',
    'model' => null,
    'fields' => [],
    'sections' => [],
    'submitButtonText' => 'Save',
    'cancelButtonText' => 'Cancel',
    'cancelRoute' => '#',
    'showCancelButton' => true,
    'isAjaxForm' => false,
])

<form id="{{ $formId }}"
      action="{{ $formAction }}"
      method="{{ in_array(strtoupper($formMethod), ['GET', 'POST']) ? $formMethod : 'POST' }}"
      enctype="{{ $enctype }}"
      class="{{ $formClasses }}"
      @if($isAjaxForm) x-data="ajaxForm" @submit.prevent="submitForm" @endif>
    
    @csrf
    
    @if(!in_array(strtoupper($formMethod), ['GET', 'POST']))
        @method($formMethod)
    @endif
    
    <div class="space-y-6">
        @if(count($sections) > 0 && (count($sections) > 1 || (isset($sections[0]['title']) && $sections[0]['title'] !== null)))
            {{-- Multiple sections or sections with titles --}}
            @foreach($sections as $section)
                <div class="rounded-md border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    @if($section['title'])
                        <h3 class="text-lg font-semibold mb-4">{{ $section['title'] }}</h3>
                    @endif
                    
                    @if(isset($section['description']))
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $section['description'] }}</p>
                    @endif
                    
                    <div class="grid grid-cols-1 gap-6 {{ $section['columns'] ?? '' }}">
                        @foreach($section['fields'] as $fieldName)
                            @if(isset($fields[$fieldName]))
                                <x-page-generator.form-field 
                                    :field="$fields[$fieldName]" 
                                    :name="$fieldName"
                                    :model="$model"
                                />
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            {{-- Flexible layout without sections --}}
            <div class="rounded-md border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($fields as $name => $field)
                        <x-page-generator.form-field 
                            :field="$field" 
                            :name="$name"
                            :model="$model"
                        />
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            @if($showCancelButton)
                <a href="{{ $cancelRoute }}" 
                   class="btn-secondary">
                    {{ $cancelButtonText }}
                </a>
            @endif
            
            <button type="submit" 
                    class="btn-primary"
                    @if($isAjaxForm) :disabled="loading" @endif>
                <span @if($isAjaxForm) x-show="!loading" @endif>{{ $submitButtonText }}</span>
                @if($isAjaxForm)
                    <span x-show="loading" class="flex items-center gap-2">
                        <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                        {{ __('Saving...') }}
                    </span>
                @endif
            </button>
        </div>
    </div>
</form>

@if($isAjaxForm)
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ajaxForm', () => ({
        loading: false,
        
        async submitForm() {
            this.loading = true;
            
            try {
                const formData = new FormData(this.$el);
                const response = await fetch(this.$el.action, {
                    method: this.$el.method.toUpperCase(),
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.message) {
                        // Show success message
                        alert(data.message);
                    }
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = this.$el.querySelector(`[name="${field}"]`);
                            if (input) {
                                const errorEl = input.parentElement.querySelector('.error-message');
                                if (errorEl) {
                                    errorEl.textContent = data.errors[field][0];
                                    errorEl.classList.remove('hidden');
                                }
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Form submission error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.loading = false;
            }
        }
    }))
});
</script>
@endpush
@endif