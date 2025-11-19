<h3 class="text-lg mb-3 font-bold p-3 pl-0">
    {{ __('Arrow Link Components') }}
</h3>

<div class="flex flex-col gap-5">
    <x-demo.preview-component
        title="{{ __('Basic Arrow Link Examples') }}"
        path="views/demo/arrow-link/basic.blade.php"
        include="demo.arrow-link.basic"
    />

    <x-demo.preview-component
        title="{{ __('Colors') }}"
        path="views/demo/arrow-link/colors-sizes.blade.php"
        include="demo.arrow-link.colors-sizes"
    />

    <x-demo.preview-component
        title="{{ __('Options') }}"
        path="views/demo/arrow-link/attributes.blade.php"
        include="demo.arrow-link.attributes"
    />
</div>