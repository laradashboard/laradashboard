<?php

declare(strict_types=1);

use App\Services\Builder\BlockService;

beforeEach(function () {
    $this->blockService = new BlockService();
});

describe('blockId', function () {
    test('generates unique block IDs', function () {
        $id1 = $this->blockService->blockId();
        $id2 = $this->blockService->blockId();

        expect($id1)->toStartWith('block_')
            ->and($id2)->toStartWith('block_')
            ->and($id1)->not->toEqual($id2);
    });

    test('block ID has correct format', function () {
        $id = $this->blockService->blockId();

        expect($id)->toMatch('/^block_[a-zA-Z0-9]{8}$/');
    });
});

describe('defaultLayoutStyles', function () {
    test('returns correct default layout structure', function () {
        $styles = $this->blockService->defaultLayoutStyles();

        expect($styles)->toBeArray()
            ->and($styles)->toHaveKeys(['margin', 'padding'])
            ->and($styles['margin'])->toHaveKeys(['top', 'right', 'bottom', 'left'])
            ->and($styles['padding'])->toHaveKeys(['top', 'right', 'bottom', 'left']);
    });

    test('default layout values are empty strings', function () {
        $styles = $this->blockService->defaultLayoutStyles();

        foreach (['margin', 'padding'] as $property) {
            foreach (['top', 'right', 'bottom', 'left'] as $direction) {
                expect($styles[$property][$direction])->toBe('');
            }
        }
    });
});

describe('heading block', function () {
    test('creates heading block with default values', function () {
        $block = $this->blockService->heading('Welcome');

        expect($block['type'])->toBe('heading')
            ->and($block['props']['text'])->toBe('Welcome')
            ->and($block['props']['level'])->toBe('h1')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['color'])->toBe('#333333')
            ->and($block['props']['fontSize'])->toBe('28px')
            ->and($block['props']['fontWeight'])->toBe('bold')
            ->and($block['id'])->toStartWith('block_');
    });

    test('creates heading block with custom values', function () {
        $block = $this->blockService->heading('Title', 'h2', 'left', '#000000', '24px');

        expect($block['props']['text'])->toBe('Title')
            ->and($block['props']['level'])->toBe('h2')
            ->and($block['props']['align'])->toBe('left')
            ->and($block['props']['color'])->toBe('#000000')
            ->and($block['props']['fontSize'])->toBe('24px');
    });

    test('heading block includes layout styles', function () {
        $block = $this->blockService->heading('Test');

        expect($block['props'])->toHaveKey('layoutStyles')
            ->and($block['props']['layoutStyles'])->toHaveKeys(['margin', 'padding']);
    });
});

describe('text block', function () {
    test('creates text block with default values', function () {
        $block = $this->blockService->text('Hello world');

        expect($block['type'])->toBe('text')
            ->and($block['props']['content'])->toBe('Hello world')
            ->and($block['props']['align'])->toBe('left')
            ->and($block['props']['color'])->toBe('#666666')
            ->and($block['props']['fontSize'])->toBe('16px')
            ->and($block['props']['lineHeight'])->toBe('1.6');
    });

    test('creates text block with custom values', function () {
        $block = $this->blockService->text('Content', 'center', '#111111', '18px');

        expect($block['props']['content'])->toBe('Content')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['color'])->toBe('#111111')
            ->and($block['props']['fontSize'])->toBe('18px');
    });
});

describe('button block', function () {
    test('creates button block with default values', function () {
        $block = $this->blockService->button('Click Me');

        expect($block['type'])->toBe('button')
            ->and($block['props']['text'])->toBe('Click Me')
            ->and($block['props']['link'])->toBe('#')
            ->and($block['props']['backgroundColor'])->toBe('#635bff')
            ->and($block['props']['textColor'])->toBe('#ffffff')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['borderRadius'])->toBe('6px')
            ->and($block['props']['padding'])->toBe('14px 28px');
    });

    test('creates button block with custom values', function () {
        $block = $this->blockService->button('Submit', 'https://example.com', '#ff0000', '#000000', 'left');

        expect($block['props']['text'])->toBe('Submit')
            ->and($block['props']['link'])->toBe('https://example.com')
            ->and($block['props']['backgroundColor'])->toBe('#ff0000')
            ->and($block['props']['textColor'])->toBe('#000000')
            ->and($block['props']['align'])->toBe('left');
    });
});

describe('image block', function () {
    test('creates image block with default values', function () {
        $block = $this->blockService->image('https://example.com/image.jpg');

        expect($block['type'])->toBe('image')
            ->and($block['props']['src'])->toBe('https://example.com/image.jpg')
            ->and($block['props']['alt'])->toBe('')
            ->and($block['props']['width'])->toBe('100%')
            ->and($block['props']['height'])->toBe('auto')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['link'])->toBe('');
    });

    test('creates image block with all custom values', function () {
        $block = $this->blockService->image('https://example.com/photo.png', 'Photo', '50%', 'left', 'https://link.com');

        expect($block['props']['src'])->toBe('https://example.com/photo.png')
            ->and($block['props']['alt'])->toBe('Photo')
            ->and($block['props']['width'])->toBe('50%')
            ->and($block['props']['align'])->toBe('left')
            ->and($block['props']['link'])->toBe('https://link.com');
    });
});

describe('spacer block', function () {
    test('creates spacer block with default height', function () {
        $block = $this->blockService->spacer();

        expect($block['type'])->toBe('spacer')
            ->and($block['props']['height'])->toBe('20px');
    });

    test('creates spacer block with custom height', function () {
        $block = $this->blockService->spacer('40px');

        expect($block['props']['height'])->toBe('40px');
    });
});

describe('divider block', function () {
    test('creates divider block with default values', function () {
        $block = $this->blockService->divider();

        expect($block['type'])->toBe('divider')
            ->and($block['props']['color'])->toBe('#e5e7eb')
            ->and($block['props']['thickness'])->toBe('1px')
            ->and($block['props']['width'])->toBe('100%')
            ->and($block['props']['style'])->toBe('solid')
            ->and($block['props']['margin'])->toBe('20px 0');
    });

    test('creates divider block with custom values', function () {
        $block = $this->blockService->divider('#000000', '2px', '80%');

        expect($block['props']['color'])->toBe('#000000')
            ->and($block['props']['thickness'])->toBe('2px')
            ->and($block['props']['width'])->toBe('80%');
    });
});

describe('list block', function () {
    test('creates bullet list block with default values', function () {
        $items = ['Item 1', 'Item 2', 'Item 3'];
        $block = $this->blockService->listBlock($items);

        expect($block['type'])->toBe('list')
            ->and($block['props']['items'])->toBe($items)
            ->and($block['props']['listType'])->toBe('bullet')
            ->and($block['props']['color'])->toBe('#666666')
            ->and($block['props']['fontSize'])->toBe('16px')
            ->and($block['props']['iconColor'])->toBe('#635bff');
    });

    test('creates numbered list block', function () {
        $items = ['First', 'Second'];
        $block = $this->blockService->listBlock($items, 'number', '#111111');

        expect($block['props']['items'])->toBe($items)
            ->and($block['props']['listType'])->toBe('number')
            ->and($block['props']['color'])->toBe('#111111');
    });
});

describe('quote block', function () {
    test('creates quote block with text only', function () {
        $block = $this->blockService->quote('Great quote');

        expect($block['type'])->toBe('quote')
            ->and($block['props']['text'])->toBe('Great quote')
            ->and($block['props']['author'])->toBe('')
            ->and($block['props']['authorTitle'])->toBe('')
            ->and($block['props']['borderColor'])->toBe('#635bff')
            ->and($block['props']['backgroundColor'])->toBe('#f8fafc');
    });

    test('creates quote block with author information', function () {
        $block = $this->blockService->quote('Famous quote', 'John Doe', 'CEO');

        expect($block['props']['text'])->toBe('Famous quote')
            ->and($block['props']['author'])->toBe('John Doe')
            ->and($block['props']['authorTitle'])->toBe('CEO');
    });
});

describe('table block', function () {
    test('creates table block with headers and rows', function () {
        $headers = ['Name', 'Email', 'Role'];
        $rows = [
            ['John', 'john@example.com', 'Admin'],
            ['Jane', 'jane@example.com', 'User'],
        ];
        $block = $this->blockService->table($headers, $rows);

        expect($block['type'])->toBe('table')
            ->and($block['props']['headers'])->toBe($headers)
            ->and($block['props']['rows'])->toBe($rows)
            ->and($block['props']['showHeader'])->toBeTrue()
            ->and($block['props']['headerBgColor'])->toBe('#f1f5f9')
            ->and($block['props']['borderColor'])->toBe('#e2e8f0')
            ->and($block['props']['cellPadding'])->toBe('12px');
    });

    test('creates table block without header', function () {
        $block = $this->blockService->table(['Col1'], [['Row1']], false);

        expect($block['props']['showHeader'])->toBeFalse();
    });
});

describe('footer block', function () {
    test('creates footer block with default values', function () {
        $block = $this->blockService->footer();

        expect($block['type'])->toBe('footer')
            ->and($block['props']['companyName'])->toBe('{app_name}')
            ->and($block['props']['address'])->toBe('')
            ->and($block['props']['email'])->toBe('')
            ->and($block['props']['phone'])->toBe('')
            ->and($block['props']['unsubscribeText'])->toBe('Unsubscribe from these emails')
            ->and($block['props']['unsubscribeUrl'])->toBe('#unsubscribe')
            ->and($block['props']['textColor'])->toBe('#6b7280')
            ->and($block['props']['align'])->toBe('center');
    });

    test('creates footer block with custom values', function () {
        $block = $this->blockService->footer('My Company', '123 Main St', 'info@company.com');

        expect($block['props']['companyName'])->toBe('My Company')
            ->and($block['props']['address'])->toBe('123 Main St')
            ->and($block['props']['email'])->toBe('info@company.com');
    });
});

describe('social block', function () {
    test('creates social block with default values', function () {
        $block = $this->blockService->social();

        expect($block['type'])->toBe('social')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['iconSize'])->toBe('32px')
            ->and($block['props']['gap'])->toBe('12px')
            ->and($block['props']['links'])->toHaveKeys(['facebook', 'twitter', 'instagram', 'linkedin', 'youtube']);
    });

    test('creates social block with custom links', function () {
        $links = ['facebook' => 'https://facebook.com/mypage', 'twitter' => 'https://twitter.com/myprofile'];
        $block = $this->blockService->social($links, 'left');

        expect($block['props']['links']['facebook'])->toBe('https://facebook.com/mypage')
            ->and($block['props']['links']['twitter'])->toBe('https://twitter.com/myprofile')
            ->and($block['props']['align'])->toBe('left');
    });
});

describe('countdown block', function () {
    test('creates countdown block with default values', function () {
        $block = $this->blockService->countdown();

        expect($block['type'])->toBe('countdown')
            ->and($block['props']['title'])->toBe('Sale Ends In')
            ->and($block['props']['targetDate'])->not->toBeEmpty()
            ->and($block['props']['targetTime'])->toBe('23:59')
            ->and($block['props']['backgroundColor'])->toBe('#1e293b')
            ->and($block['props']['textColor'])->toBe('#ffffff')
            ->and($block['props']['numberColor'])->toBe('#635bff')
            ->and($block['props']['expiredMessage'])->toBe('This offer has expired!');
    });

    test('creates countdown block with custom target date', function () {
        $block = $this->blockService->countdown('Ends Soon', '2025-12-31');

        expect($block['props']['title'])->toBe('Ends Soon')
            ->and($block['props']['targetDate'])->toBe('2025-12-31');
    });
});

describe('video block', function () {
    test('creates video block with default values', function () {
        $block = $this->blockService->video();

        expect($block['type'])->toBe('video')
            ->and($block['props']['thumbnailUrl'])->toBe('')
            ->and($block['props']['videoUrl'])->toBe('')
            ->and($block['props']['alt'])->toBe('Video')
            ->and($block['props']['width'])->toBe('100%')
            ->and($block['props']['align'])->toBe('center')
            ->and($block['props']['playButtonColor'])->toBe('#635bff');
    });

    test('creates video block with custom values', function () {
        $block = $this->blockService->video('https://example.com/thumb.jpg', 'https://example.com/video.mp4', 'My Video');

        expect($block['props']['thumbnailUrl'])->toBe('https://example.com/thumb.jpg')
            ->and($block['props']['videoUrl'])->toBe('https://example.com/video.mp4')
            ->and($block['props']['alt'])->toBe('My Video');
    });
});

describe('renderBlock', function () {
    test('renders heading block correctly', function () {
        $block = $this->blockService->heading('Hello World', 'h2', 'left', '#000000', '24px');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<h2')
            ->and($html)->toContain('Hello World')
            ->and($html)->toContain('text-align: left')
            ->and($html)->toContain('color: #000000')
            ->and($html)->toContain('font-size: 24px')
            ->and($html)->toContain('</h2>');
    });

    test('renders text block correctly', function () {
        $block = $this->blockService->text('Some content', 'center', '#333333', '18px');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<div')
            ->and($html)->toContain('Some content')
            ->and($html)->toContain('text-align: center')
            ->and($html)->toContain('color: #333333')
            ->and($html)->toContain('font-size: 18px');
    });

    test('renders button block correctly', function () {
        $block = $this->blockService->button('Click Here', 'https://example.com', '#ff0000', '#ffffff', 'center');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<a')
            ->and($html)->toContain('href="https://example.com"')
            ->and($html)->toContain('Click Here')
            ->and($html)->toContain('background-color: #ff0000')
            ->and($html)->toContain('color: #ffffff');
    });

    test('renders image block correctly', function () {
        $block = $this->blockService->image('https://example.com/image.jpg', 'Alt text', '80%', 'center');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<img')
            ->and($html)->toContain('src="https://example.com/image.jpg"')
            ->and($html)->toContain('alt="Alt text"')
            ->and($html)->toContain('max-width: 80%');
    });

    test('renders image block with link', function () {
        $block = $this->blockService->image('https://example.com/image.jpg', 'Alt', '100%', 'center', 'https://link.com');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<a href="https://link.com"')
            ->and($html)->toContain('target="_blank"');
    });

    test('renders empty string for image with no src', function () {
        $html = $this->blockService->renderImage(['src' => '']);

        expect($html)->toBe('');
    });

    test('renders spacer block correctly', function () {
        $block = $this->blockService->spacer('30px');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('height: 30px');
    });

    test('renders divider block correctly', function () {
        $block = $this->blockService->divider('#cccccc', '2px', '90%');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<hr')
            ->and($html)->toContain('border-top: 2px solid #cccccc')
            ->and($html)->toContain('width: 90%');
    });

    test('renders bullet list correctly', function () {
        $block = $this->blockService->listBlock(['Item A', 'Item B'], 'bullet');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<ul')
            ->and($html)->toContain('<li')
            ->and($html)->toContain('Item A')
            ->and($html)->toContain('Item B')
            ->and($html)->toContain('</ul>');
    });

    test('renders numbered list correctly', function () {
        $block = $this->blockService->listBlock(['First', 'Second'], 'number');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<ol')
            ->and($html)->toContain('</ol>');
    });

    test('renders empty string for list with no items', function () {
        $html = $this->blockService->renderList(['items' => []]);

        expect($html)->toBe('');
    });

    test('renders quote block correctly', function () {
        $block = $this->blockService->quote('Great quote', 'John Doe', 'CEO');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('"Great quote"')
            ->and($html)->toContain('John Doe')
            ->and($html)->toContain('CEO')
            ->and($html)->toContain('border-left: 4px solid');
    });

    test('renders quote block without author', function () {
        $block = $this->blockService->quote('Just a quote');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('"Just a quote"')
            ->and($html)->not->toContain('font-weight: 600; margin: 12px');
    });

    test('renders table block correctly', function () {
        $headers = ['Name', 'Email'];
        $rows = [['John', 'john@test.com']];
        $block = $this->blockService->table($headers, $rows);
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('<table')
            ->and($html)->toContain('<thead>')
            ->and($html)->toContain('<th')
            ->and($html)->toContain('Name')
            ->and($html)->toContain('<tbody>')
            ->and($html)->toContain('<td')
            ->and($html)->toContain('john@test.com');
    });

    test('renders table block without header when disabled', function () {
        $block = $this->blockService->table(['Col'], [['Data']], false);
        $html = $this->blockService->renderBlock($block);

        expect($html)->not->toContain('<thead>');
    });

    test('renders footer block correctly', function () {
        $block = $this->blockService->footer('My Company', '123 Main St', 'info@example.com');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('My Company')
            ->and($html)->toContain('123 Main St')
            ->and($html)->toContain('mailto:info@example.com')
            ->and($html)->toContain('Unsubscribe');
    });

    test('renders social block correctly', function () {
        $links = ['facebook' => 'https://facebook.com/page'];
        $block = $this->blockService->social($links);
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('href="https://facebook.com/page"')
            ->and($html)->toContain('alt="facebook"');
    });

    test('renders empty string for social block with no links', function () {
        $html = $this->blockService->renderSocial(['links' => []]);

        expect($html)->toBe('');
    });

    test('renders countdown block correctly', function () {
        $block = $this->blockService->countdown('Hurry!', '2025-12-31');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('Hurry!')
            ->and($html)->toContain('Days')
            ->and($html)->toContain('Hours')
            ->and($html)->toContain('Minutes')
            ->and($html)->toContain('Seconds');
    });

    test('renders video block with thumbnail', function () {
        $block = $this->blockService->video('https://example.com/thumb.jpg', 'https://example.com/video.mp4', 'Video');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('src="https://example.com/thumb.jpg"')
            ->and($html)->toContain('href="https://example.com/video.mp4"');
    });

    test('renders video block placeholder when no thumbnail', function () {
        $block = $this->blockService->video('', '', 'My Video');
        $html = $this->blockService->renderBlock($block);

        expect($html)->toContain('My Video')
            ->and($html)->toContain('background-color: #1e293b');
    });

    test('returns empty string for unknown block type', function () {
        $html = $this->blockService->renderBlock(['type' => 'unknown', 'props' => []]);

        expect($html)->toBe('');
    });
});

describe('parseBlocks', function () {
    test('parses empty blocks array', function () {
        $html = $this->blockService->parseBlocks([]);

        expect($html)->toBe('');
    });

    test('parses single block', function () {
        $blocks = [
            $this->blockService->heading('Title'),
        ];
        $html = $this->blockService->parseBlocks($blocks);

        expect($html)->toContain('<h1')
            ->and($html)->toContain('Title')
            ->and($html)->toContain('</h1>');
    });

    test('parses multiple blocks in order', function () {
        $blocks = [
            $this->blockService->heading('Welcome'),
            $this->blockService->text('Hello world'),
            $this->blockService->button('Click Me', 'https://example.com'),
        ];
        $html = $this->blockService->parseBlocks($blocks);

        expect($html)->toContain('Welcome')
            ->and($html)->toContain('Hello world')
            ->and($html)->toContain('Click Me');

        // Verify order: heading comes before text, text before button
        $headingPos = strpos($html, 'Welcome');
        $textPos = strpos($html, 'Hello world');
        $buttonPos = strpos($html, 'Click Me');

        expect($headingPos)->toBeLessThan($textPos)
            ->and($textPos)->toBeLessThan($buttonPos);
    });

    test('parses complex email template blocks', function () {
        $blocks = [
            $this->blockService->heading('Newsletter'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Check out our latest updates'),
            $this->blockService->divider(),
            $this->blockService->listBlock(['Feature 1', 'Feature 2', 'Feature 3']),
            $this->blockService->button('Learn More', 'https://example.com'),
            $this->blockService->spacer('40px'),
            $this->blockService->footer('My Company'),
        ];
        $html = $this->blockService->parseBlocks($blocks);

        expect($html)->toContain('Newsletter')
            ->and($html)->toContain('Check out our latest updates')
            ->and($html)->toContain('<hr')
            ->and($html)->toContain('Feature 1')
            ->and($html)->toContain('Feature 2')
            ->and($html)->toContain('Feature 3')
            ->and($html)->toContain('Learn More')
            ->and($html)->toContain('My Company');
    });
});

describe('render methods handle missing props gracefully', function () {
    test('renderHeading uses defaults for missing props', function () {
        $html = $this->blockService->renderHeading([]);

        expect($html)->toContain('<h1')
            ->and($html)->toContain('text-align: center')
            ->and($html)->toContain('color: #333333');
    });

    test('renderText uses defaults for missing props', function () {
        $html = $this->blockService->renderText([]);

        expect($html)->toContain('text-align: left')
            ->and($html)->toContain('color: #666666');
    });

    test('renderButton uses defaults for missing props', function () {
        $html = $this->blockService->renderButton([]);

        expect($html)->toContain('Click Here')
            ->and($html)->toContain('href="#"');
    });

    test('renderSpacer uses defaults for missing props', function () {
        $html = $this->blockService->renderSpacer([]);

        expect($html)->toContain('height: 20px');
    });

    test('renderDivider uses defaults for missing props', function () {
        $html = $this->blockService->renderDivider([]);

        expect($html)->toContain('border-top: 1px solid #e5e7eb');
    });

    test('renderQuote uses defaults for missing props', function () {
        $html = $this->blockService->renderQuote([]);

        expect($html)->toContain('""');
    });

    test('renderTable handles empty data', function () {
        $html = $this->blockService->renderTable([]);

        expect($html)->toContain('<table')
            ->and($html)->toContain('<tbody></tbody>');
    });

    test('renderFooter uses defaults for missing props', function () {
        $html = $this->blockService->renderFooter([]);

        expect($html)->toContain('Unsubscribe');
    });

    test('renderCountdown uses defaults for missing props', function () {
        $html = $this->blockService->renderCountdown([]);

        expect($html)->toContain('Sale Ends In');
    });

    test('renderVideo handles empty props', function () {
        $html = $this->blockService->renderVideo([]);

        expect($html)->toContain('Video'); // Default alt text
    });
});
