<?php

declare(strict_types=1);

use App\Support\Security\RichHtmlSanitizer;

describe('RichHtmlSanitizer::clean', function () {
    test('keeps ordinary rich text byte-for-byte', function (string $html) {
        expect(RichHtmlSanitizer::clean($html))->toBe($html);
    })->with([
        'paragraph' => '<p>Hello <strong>world</strong></p>',
        'link' => '<a href="https://example.com/docs" target="_blank" rel="noopener">docs</a>',
        'relative and anchor links' => '<a href="/about">About</a> <a href="#top">Top</a> <a href="mailto:a@b.co">Mail</a>',
        'layout with ids and data' => '<section id="hero" class="x" data-foo="bar"><div style="color:#fff;padding:4px">Hi</div></section>',
        'style element' => '<style>.card:hover { transform: scale(1.05); }</style><div class="card">x</div>',
        'https iframe embed' => '<iframe src="https://www.youtube.com/embed/abc" allowfullscreen></iframe>',
        'raster data image' => '<img src="data:image/png;base64,iVBORw0KGgo=" alt="dot">',
        'details open' => '<details open><summary>More</summary>Body</details>',
        'plain text' => 'No tags here &amp; entities stay',
    ]);

    test('keeps an email document wrapper intact', function () {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
</head>
<body style="margin: 0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center">Hi</td></tr></table>
</body>
</html>
HTML;

        expect(RichHtmlSanitizer::clean($html))->toBe($html);
    });

    test('removes script elements and event handlers', function () {
        $clean = RichHtmlSanitizer::clean('<img src="x" onerror="alert(document.domain)"><script>alert(1)</script><p>ok</p>');

        expect($clean)->toBe('<img src="x"><p>ok</p>');
    });

    test('neutralizes known XSS vectors', function (string $payload) {
        $clean = strtolower(RichHtmlSanitizer::clean($payload));
        // Quoted attribute values are inert text; only look for handlers outside them.
        $outsideQuotes = preg_replace('/"[^"]*"|\'[^\']*\'/', '""', $clean);

        expect($outsideQuotes)->not->toMatch('/<[a-z][^>]*[\s\/"\']on[a-z]+\s*=/')
            ->and($clean)
            ->not->toContain('<script')
            ->not->toContain('javascript:')
            ->not->toContain('x-init')
            ->not->toContain('@click')
            ->not->toContain('srcdoc');
    })->with([
        'unquoted handler' => '<img src=x onerror=alert(1)>',
        'slash separated' => '<img/src=x/onerror=alert(1)>',
        'no space after quote' => '<img src="x"onerror="alert(1)">',
        'quoted gt in attribute' => '<a title="x>y" onclick="alert(1)">z</a>',
        'quote in attribute name' => '<a "x=\'>\' onclick=alert(1)>z</a>',
        'javascript url' => '<a href="javascript:alert(1)">x</a>',
        'entity encoded javascript url' => '<a href="&#106;avascript:alert(1)">x</a>',
        'entity without semicolon' => '<a href="&#106avascript:alert(1)">x</a>',
        'newline in scheme' => '<a href="java&#10;script:alert(1)">x</a>',
        'tab in scheme' => "<a href=\"java\tscript:alert(1)\">x</a>",
        'named entity colon' => '<a href="javascript&colon;alert(1)">x</a>',
        'svg onload' => '<svg onload=alert(1)></svg>',
        'svg animate values' => '<svg><a><animate attributeName="href" values="javascript:alert(1)"/><text>x</text></a></svg>',
        'details ontoggle' => '<details open ontoggle=alert(1)>',
        'form action' => '<form action="javascript:alert(1)"><button>go</button></form>',
        'formaction' => '<button formaction="javascript:alert(1)">go</button>',
        'iframe srcdoc' => '<iframe srcdoc="<script>alert(1)</script>"></iframe>',
        'iframe javascript src' => '<iframe src="javascript:alert(1)"></iframe>',
        'alpine x-init' => '<div x-data x-init="alert(1)">x</div>',
        'alpine shorthand' => '<button @click="alert(1)">x</button>',
        'doubled script' => '<script><script>alert(1)</script>',
        'uppercase script' => '<SCRIPT>alert(1)</SCRIPT>',
        'empty comment breakout' => '<!--><img src=x onerror=alert(1)>-->',
        'comment breakout via attribute' => '<!-- <p title="--><img src=x onerror=alert(1)>"> -->',
        'noscript breakout' => '<noscript><p title="</noscript><img src=x onerror=alert(1)>"></noscript>',
        'style breakout' => '<style><p title="</style><img src=x onerror=alert(1)>"></style>',
        'textarea breakout' => '<textarea><p title="</textarea><img src=x onerror=alert(1)>"></textarea>',
        'svg style mxss' => '<svg><style><img src=x onerror=alert(1)></style></svg>',
        'bogus comment' => '<!x><img src=x onerror=alert(1)>',
        'cdata in svg' => '<svg><![CDATA[><img src=x onerror=alert(1)>]]></svg>',
        'end tag with attributes' => '</p onclick=alert(1)><b>x</b>',
        'unterminated tag' => '<img src=x onerror=alert(1)',
        'null byte' => "<img src=x o\0nerror=alert(1)>",
    ]);

    test('removes active elements', function (string $payload) {
        expect(strtolower(RichHtmlSanitizer::clean($payload)))
            ->not->toMatch('/<(script|object|embed|base|applet|frame|frameset|set|animate)\b/')
            ->not->toContain('http-equiv="refresh"');
    })->with([
        '<object data="https://evil.test/x.swf"></object>',
        '<embed src="https://evil.test/x.swf">',
        '<base href="https://evil.test/">',
        '<meta http-equiv="refresh" content="0;url=https://evil.test">',
        '<svg><set attributeName="onmouseover" to="alert(1)"/></svg>',
        '<frameset><frame src="https://evil.test"></frameset>',
    ]);

    test('quotes ambiguous unquoted attribute values so no parser can split them', function () {
        expect(RichHtmlSanitizer::clean('<img/src=x/onerror=alert(1)>'))->toBe('<img/src="x/onerror=alert(1)">')
            ->and(RichHtmlSanitizer::clean('<img src=a.png alt=hi>'))->toBe('<img src=a.png alt=hi>');
    });

    test('removes non-image data urls', function () {
        expect(RichHtmlSanitizer::clean('<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>'))->toBe('<a>x</a>');
    });
});

describe('RichHtmlSanitizer::sanitizeBlockProps', function () {
    test('sanitizes html and text-editor block fields', function () {
        $html = RichHtmlSanitizer::sanitizeBlockProps('html', ['code' => '<img src="x" onerror="alert(1)">']);
        $editor = RichHtmlSanitizer::sanitizeBlockProps('text-editor', ['content' => '<p>Safe</p><img src="x" onerror="alert(1)">']);

        expect($html['code'])->toBe('<img src="x">')
            ->and($editor['content'])->toBe('<p>Safe</p><img src="x">');
    });

    test('sanitizes heading, text, quote and button labels', function (string $type, string $field) {
        $props = RichHtmlSanitizer::sanitizeBlockProps($type, [$field => 'Hi <img src=x onerror=alert(1)>']);

        expect($props[$field])->toBe('Hi <img src=x>');
    })->with([
        ['heading', 'text'],
        ['text', 'content'],
        ['text', 'text'],
        ['quote', 'text'],
        ['button', 'text'],
    ]);

    test('sanitizes list items and accordion content', function () {
        $list = RichHtmlSanitizer::sanitizeBlockProps('list', ['items' => ['<img src="x" onerror="alert(1)">', 'plain']]);
        $accordion = RichHtmlSanitizer::sanitizeBlockProps('accordion', [
            'items' => [['title' => 'One', 'content' => '<script>alert(1)</script><p>Body</p>']],
        ]);

        expect($list['items'])->toBe(['<img src="x">', 'plain'])
            ->and($accordion['items'][0])->toBe(['title' => 'One', 'content' => '<p>Body</p>']);
    });

    test('blanks javascript urls in link props, including nested repeaters', function () {
        $props = RichHtmlSanitizer::sanitizeBlockProps('button-group', [
            'link' => 'javascript:alert(1)',
            'linkUrl' => ' JaVaScRiPt:alert(1)',
            'buttons' => [['text' => 'Go', 'link' => 'javascript:alert(1)'], ['text' => 'Ok', 'link' => '/ok']],
            'links' => ['facebook' => 'javascript:alert(1)', 'x' => 'https://x.com/acme'],
            'src' => 'https://cdn.test/a.png',
            'color' => 'javascript:not-a-url-field',
        ]);

        expect($props['link'])->toBe('')
            ->and($props['linkUrl'])->toBe('')
            ->and($props['buttons'][0]['link'])->toBe('')
            ->and($props['buttons'][1]['link'])->toBe('/ok')
            ->and($props['links'])->toBe(['facebook' => '', 'x' => 'https://x.com/acme'])
            ->and($props['src'])->toBe('https://cdn.test/a.png')
            ->and($props['color'])->toBe('javascript:not-a-url-field');
    });

    test('does not rewrite a code block source field', function () {
        $source = '<script>alert(1)</script>';

        expect(RichHtmlSanitizer::sanitizeBlockProps('code', ['code' => $source])['code'])->toBe($source);
    });
});

describe('RichHtmlSanitizer::sanitizeDesign', function () {
    test('cleans nested blocks, editor-only fields and unknown block types, but keeps code samples', function () {
        $xss = '<img src=x onerror=alert(1)>';
        $design = [
            'blocks' => [
                ['id' => 'q', 'type' => 'quote', 'props' => ['text' => 'Hi', 'author' => $xss, 'authorTitle' => $xss]],
                ['id' => 'p', 'type' => 'preformatted', 'props' => ['text' => $xss]],
                ['id' => 'm', 'type' => 'my-module-block', 'props' => ['body' => $xss, 'cta' => ['link' => 'javascript:alert(1)']]],
                ['id' => 'c', 'type' => 'columns', 'props' => ['columns' => 2, 'children' => [
                    [['id' => 't', 'type' => 'text', 'props' => ['content' => $xss]]],
                    [['id' => 'k', 'type' => 'code', 'props' => ['code' => '<script>alert(1)</script>']]],
                ]]],
            ],
            'canvasSettings' => ['backgroundColor' => '#fff'],
        ];

        $clean = RichHtmlSanitizer::sanitizeDesign($design);
        $blocks = $clean['blocks'];

        expect($blocks[0]['props'])->toBe(['text' => 'Hi', 'author' => '<img src=x>', 'authorTitle' => '<img src=x>'])
            ->and($blocks[1]['props']['text'])->toBe('<img src=x>')
            ->and($blocks[2]['props'])->toBe(['body' => '<img src=x>', 'cta' => ['link' => '']])
            ->and($blocks[3]['props']['children'][0][0]['props']['content'])->toBe('<img src=x>')
            ->and($blocks[3]['props']['children'][1][0]['props']['code'])->toBe('<script>alert(1)</script>')
            ->and($clean['canvasSettings'])->toBe(['backgroundColor' => '#fff']);
    });

    test('leaves a clean design unchanged', function () {
        $design = [['id' => 'h', 'type' => 'heading', 'props' => ['text' => 'Title <strong>bold</strong>', 'level' => 'h2']]];

        expect(RichHtmlSanitizer::sanitizeDesign($design))->toBe($design);
    });
});

describe('RichHtmlSanitizer::neutralizeDocument', function () {
    test('strips a script beside a builder placeholder without breaking the placeholder', function () {
        $placeholder = '<div data-lara-block="html" data-props=\'{"code":"<img src=x onerror=alert(1)>","layoutStyles":{}}\'></div>';
        $clean = RichHtmlSanitizer::neutralizeDocument('<script>alert(1)</script>'.$placeholder);

        // The placeholder is left byte-identical; its props are cleaned when the block renders.
        expect($clean)->toBe($placeholder);
    });
});
