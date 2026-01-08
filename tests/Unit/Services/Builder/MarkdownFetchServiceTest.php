<?php

use App\Services\Builder\MarkdownFetchService;

beforeEach(function () {
    $this->service = new MarkdownFetchService();
});

describe('toRawUrl', function () {
    test('converts GitHub blob URL to raw URL', function () {
        $url = 'https://github.com/user/repo/blob/main/README.md';
        $expected = 'https://raw.githubusercontent.com/user/repo/main/README.md';

        expect($this->service->toRawUrl($url))->toBe($expected);
    });

    test('converts GitHub blob URL with branch to raw URL', function () {
        $url = 'https://github.com/laradashboard/laradashboard/blob/develop/docs/readme.md';
        $expected = 'https://raw.githubusercontent.com/laradashboard/laradashboard/develop/docs/readme.md';

        expect($this->service->toRawUrl($url))->toBe($expected);
    });

    test('converts GitHub refs/heads URL to raw URL', function () {
        $url = 'https://raw.githubusercontent.com/user/repo/refs/heads/main/README.md';
        $expected = 'https://raw.githubusercontent.com/user/repo/main/README.md';

        expect($this->service->toRawUrl($url))->toBe($expected);
    });

    test('converts GitLab blob URL to raw URL', function () {
        $url = 'https://gitlab.com/user/repo/-/blob/main/README.md';
        $expected = 'https://gitlab.com/user/repo/-/raw/main/README.md';

        expect($this->service->toRawUrl($url))->toBe($expected);
    });

    test('converts Bitbucket src URL to raw URL', function () {
        $url = 'https://bitbucket.org/user/repo/src/main/README.md';
        $expected = 'https://bitbucket.org/user/repo/raw/main/README.md';

        expect($this->service->toRawUrl($url))->toBe($expected);
    });

    test('returns raw URLs unchanged', function () {
        $url = 'https://raw.githubusercontent.com/user/repo/main/README.md';

        expect($this->service->toRawUrl($url))->toBe($url);
    });

    test('returns other URLs unchanged', function () {
        $url = 'https://example.com/docs/readme.md';

        expect($this->service->toRawUrl($url))->toBe($url);
    });
});

describe('isSupportedSource', function () {
    test('recognizes GitHub URLs as supported', function () {
        expect($this->service->isSupportedSource('https://github.com/user/repo/blob/main/README.md'))->toBeTrue();
        expect($this->service->isSupportedSource('https://raw.githubusercontent.com/user/repo/main/README.md'))->toBeTrue();
    });

    test('recognizes GitLab URLs as supported', function () {
        expect($this->service->isSupportedSource('https://gitlab.com/user/repo/-/blob/main/README.md'))->toBeTrue();
    });

    test('recognizes Bitbucket URLs as supported', function () {
        expect($this->service->isSupportedSource('https://bitbucket.org/user/repo/src/main/README.md'))->toBeTrue();
    });

    test('recognizes any .md file URL as supported', function () {
        expect($this->service->isSupportedSource('https://example.com/docs/readme.md'))->toBeTrue();
        expect($this->service->isSupportedSource('https://custom.domain.com/path/to/file.MD'))->toBeTrue();
    });

    test('rejects non-markdown URLs from unsupported domains', function () {
        expect($this->service->isSupportedSource('https://example.com/page.html'))->toBeFalse();
        expect($this->service->isSupportedSource('https://random.com/file.txt'))->toBeFalse();
    });
});

describe('convertToHtml', function () {
    test('converts markdown headings to HTML', function () {
        $markdown = '# Hello World';
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<h1>Hello World</h1>');
    });

    test('converts markdown paragraphs to HTML', function () {
        $markdown = 'This is a paragraph.';
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<p>This is a paragraph.</p>');
    });

    test('converts markdown lists to HTML', function () {
        $markdown = "- Item 1\n- Item 2\n- Item 3";
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<ul>');
        expect($html)->toContain('<li>Item 1</li>');
    });

    test('converts markdown code blocks to HTML', function () {
        $markdown = "```php\necho 'hello';\n```";
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<code');
        expect($html)->toContain('echo');
    });

    test('converts markdown tables to HTML', function () {
        $markdown = "| Header 1 | Header 2 |\n|----------|----------|\n| Cell 1 | Cell 2 |";
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<table>');
        expect($html)->toContain('<th>');
        expect($html)->toContain('<td>');
    });

    test('converts markdown task lists to HTML', function () {
        $markdown = "- [x] Done\n- [ ] Todo";
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('type="checkbox"');
    });

    test('converts markdown links to HTML', function () {
        $markdown = '[Link text](https://example.com)';
        $html = $this->service->convertToHtml($markdown);

        expect($html)->toContain('<a href="https://example.com">Link text</a>');
    });
});

describe('fetchAndConvert', function () {
    test('returns error for invalid URL', function () {
        $result = $this->service->fetchAndConvert('not-a-valid-url');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('Invalid URL format');
    });
});
