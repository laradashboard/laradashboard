<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationsExtractCommandTest extends TestCase
{
    protected string $enJsonPath;

    protected string $originalContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enJsonPath = resource_path('lang/en.json');
        $this->originalContent = File::get($this->enJsonPath);
    }

    protected function tearDown(): void
    {
        // Restore original en.json
        File::put($this->enJsonPath, $this->originalContent);

        parent::tearDown();
    }

    public function test_dry_run_does_not_modify_en_json(): void
    {
        $before = File::get($this->enJsonPath);

        $this->artisan('translations:extract', ['--dry-run' => true])
            ->assertExitCode(0);

        $after = File::get($this->enJsonPath);
        $this->assertSame($before, $after);
    }

    public function test_extracted_strings_are_added_with_key_equals_value(): void
    {
        // Start with a minimal en.json to force new keys to be found
        File::put($this->enJsonPath, json_encode(['Dashboard' => 'Dashboard'], JSON_PRETTY_PRINT) . "\n");

        $this->artisan('translations:extract')
            ->assertExitCode(0);

        $result = json_decode(File::get($this->enJsonPath), true);

        // Should have more than just our one key
        $this->assertGreaterThan(1, count($result));

        // Every new key should have value equal to the key (English self-reference)
        foreach ($result as $key => $value) {
            $this->assertSame($key, $value, "Key '{$key}' should have value equal to itself");
        }
    }

    public function test_output_is_sorted_alphabetically(): void
    {
        File::put($this->enJsonPath, json_encode(['Zebra' => 'Zebra', 'Apple' => 'Apple'], JSON_PRETTY_PRINT) . "\n");

        $this->artisan('translations:extract')
            ->assertExitCode(0);

        $result = json_decode(File::get($this->enJsonPath), true);
        $keys = array_keys($result);
        $sorted = $keys;
        sort($sorted);

        $this->assertSame($sorted, $keys, 'Keys should be sorted alphabetically');
    }

    public function test_php_file_based_keys_are_skipped(): void
    {
        File::put($this->enJsonPath, json_encode([], JSON_PRETTY_PRINT) . "\n");

        $this->artisan('translations:extract')
            ->assertExitCode(0);

        $result = json_decode(File::get($this->enJsonPath), true);

        // PHP file-based keys (dot-notation) should not appear
        $this->assertArrayNotHasKey('auth.failed', $result);
        $this->assertArrayNotHasKey('validation.required', $result);
        $this->assertArrayNotHasKey('passwords.reset', $result);
    }

    public function test_empty_strings_are_skipped(): void
    {
        File::put($this->enJsonPath, json_encode([], JSON_PRETTY_PRINT) . "\n");

        $this->artisan('translations:extract')
            ->assertExitCode(0);

        $result = json_decode(File::get($this->enJsonPath), true);

        $this->assertArrayNotHasKey('', $result);
        $this->assertArrayNotHasKey(' ', $result);
    }

    public function test_command_runs_successfully_on_real_codebase(): void
    {
        $this->artisan('translations:extract')
            ->assertExitCode(0);

        $result = json_decode(File::get($this->enJsonPath), true);
        $this->assertNotNull($result, 'en.json should contain valid JSON after extraction');
        $this->assertIsArray($result);
    }
}
