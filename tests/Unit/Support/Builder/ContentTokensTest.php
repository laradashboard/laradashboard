<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Builder;

use App\Support\Builder\ContentTokens;
use Tests\TestCase;

class ContentTokensTest extends TestCase
{
    public function test_legacy_colors_are_treated_as_inherit(): void
    {
        $this->assertTrue(ContentTokens::shouldInheritContentColor('#666666'));
        $this->assertTrue(ContentTokens::shouldInheritContentColor('#333333'));
        $this->assertTrue(ContentTokens::shouldInheritContentColor(''));
        $this->assertTrue(ContentTokens::shouldInheritContentColor(null));
    }

    public function test_custom_colors_are_not_inherited(): void
    {
        $this->assertFalse(ContentTokens::shouldInheritContentColor('#ff0000'));
    }

    public function test_page_text_color_returns_null_for_inherited_values(): void
    {
        $this->assertNull(ContentTokens::resolvePageTextColor('#666666'));
        $this->assertSame('#ff0000', ContentTokens::resolvePageTextColor('#ff0000'));
    }

    public function test_email_text_color_uses_token_fallback(): void
    {
        $this->assertSame(
            ContentTokens::TEXT,
            ContentTokens::resolveEmailTextColor('#666666')
        );
    }

    public function test_section_text_color_styles_are_empty_when_inherited(): void
    {
        $this->assertSame([], ContentTokens::sectionTextColorStyles(''));
        $this->assertSame([], ContentTokens::sectionTextColorStyles('#666666'));
    }

    public function test_section_text_color_styles_set_variables_when_custom(): void
    {
        $styles = ContentTokens::sectionTextColorStyles('#ffffff');

        $this->assertContains('color: #ffffff', $styles);
        $this->assertContains('--lb-color-text: #ffffff', $styles);
        $this->assertContains('--lb-color-heading: #ffffff', $styles);
    }

    public function test_page_wrapper_includes_css_variables(): void
    {
        $styles = ContentTokens::pageWrapperVariableStyles();

        $this->assertContains('--lb-color-text: ' . ContentTokens::TEXT, $styles);
        $this->assertContains('color: var(--lb-color-text)', $styles);
    }
}
