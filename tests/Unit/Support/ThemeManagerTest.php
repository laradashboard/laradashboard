<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ThemeManager;
use Tests\TestCase;

class ThemeManagerTest extends TestCase
{
    private ThemeManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new ThemeManager();
    }

    public function test_active_returns_configured_theme(): void
    {
        config(['settings.active_theme' => 'starter26']);

        $this->assertEquals('starter26', $this->manager->active());
    }

    public function test_active_returns_null_when_not_set(): void
    {
        config(['settings.active_theme' => null]);

        $this->assertNull($this->manager->active());
    }

    public function test_setting_reads_from_settings_config(): void
    {
        config(['settings.posts_per_page' => 24]);

        $this->assertEquals(24, $this->manager->setting('posts_per_page'));
    }

    public function test_setting_returns_default_when_not_set(): void
    {
        $this->assertEquals('fallback', $this->manager->setting('completely_nonexistent_key_xyz', 'fallback'));
    }

    public function test_layout_returns_theme_layout_path(): void
    {
        config(['settings.active_theme' => 'starter26']);

        $this->assertEquals('starter26::layouts.app', $this->manager->layout());
    }

    public function test_view_prefix_returns_theme_prefix(): void
    {
        config(['settings.active_theme' => 'starter26']);

        $this->assertEquals('starter26::', $this->manager->viewPrefix());
    }

    public function test_is_active_returns_true_for_active_theme(): void
    {
        config(['settings.active_theme' => 'starter26']);

        $this->assertTrue($this->manager->isActive('starter26'));
        $this->assertFalse($this->manager->isActive('othertheme'));
    }

    public function test_register_defaults_only_sets_null_values(): void
    {
        config(['settings.existing_key' => 'original']);
        config(['settings.new_key' => null]);

        $this->manager->registerDefaults([
            'existing_key' => 'overwritten',
            'new_key' => 'default_value',
        ]);

        $this->assertEquals('original', config('settings.existing_key'));
        $this->assertEquals('default_value', config('settings.new_key'));
    }
}
