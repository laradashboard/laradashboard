{{--
    Speculation Rules API — Prefetch links for instant navigation.
    @see https://developer.chrome.com/docs/web-platform/prerender-pages

    Browsers that don't support the Speculation Rules API will safely ignore
    the <script type="speculationrules"> tag, so no polyfill is needed.
--}}
@php
    $excludePatterns = array_merge([
        '/admin/*',
        '/login',
        '/register',
        '/logout',
        '/api/*',
        '/livewire/*',
        '/_debugbar/*',
    ], $excludePatterns ?? []);

    $notClauses = array_map(fn($pattern) => ['not' => ['href_matches' => $pattern]], $excludePatterns);

    $rules = json_encode([
        'prefetch' => [
            [
                'where' => [
                    'and' => array_merge(
                        [['href_matches' => '/*']],
                        $notClauses
                    ),
                ],
                'eagerness' => 'moderate',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
<script type="speculationrules">{!! $rules !!}</script>
