@props([])

<div
    {{ $attributes->class('datatable-unified-scroll-header sticky top-0 z-20 -mx-4 bg-body px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-gray-900 [&>div]:mb-0') }}
    data-datatable-unified-scroll-header
    x-data="{
        observer: null,
        setHeaderHeight() {
            document.documentElement.style.setProperty('--admin-page-header-height', this.$el.offsetHeight + 'px');
        },
        init() {
            document.body.classList.add('admin-unified-scroll');

            const nav = document.getElementById('appHeader');
            if (nav) {
                nav.classList.remove('sticky');
                nav.classList.add('relative');
            }

            this.setHeaderHeight();
            this.observer = new ResizeObserver(() => this.setHeaderHeight());
            this.observer.observe(this.$el);
        },
        destroy() {
            document.body.classList.remove('admin-unified-scroll');
            document.documentElement.style.removeProperty('--admin-page-header-height');
            this.observer?.disconnect();

            const nav = document.getElementById('appHeader');
            if (nav) {
                nav.classList.add('sticky');
                nav.classList.remove('relative');
            }
        }
    }"
>
    {{ $slot }}
</div>
