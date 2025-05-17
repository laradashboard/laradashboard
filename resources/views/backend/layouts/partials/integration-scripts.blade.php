@if (!empty(config('settings.google_tag_manager_script')))
    {!! config('settings.google_tag_manager_script') !!}
@endif

@if(env('DEMO_MODE', false))
    <script
        async
        src="https://www.googletagmanager.com/gtag/js?id=G-WWCRYQMHZ7"
    ></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag("js", new Date());
        gtag("config", "G-WWCRYQMHZ7");
    </script>
@elseif (!empty(config('settings.google_analytics_script')))
    {!! config('settings.google_analytics_script') !!}
@endif

