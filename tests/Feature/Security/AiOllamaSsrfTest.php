<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use App\Services\AiContentGeneratorService;
use App\Support\Security\OllamaUrlValidator;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

/*
 * The Ollama base URL is a setting the server sends requests to. It must not be usable to
 * reach cloud metadata or arbitrary internal endpoints, only a Superadmin may change it,
 * and generate-seo may only use providers that are actually configured.
 */

beforeEach(function () {
    $this->setUpSecurityUsers();

    // An ordinary content editor: post.create only.
    $this->editor = $this->createUserWithRole(Role::ADMIN, ['post.create']);

    // An admin who can edit settings but is not a Superadmin.
    $this->settingsAdmin = $this->createUserWithRole(Role::ADMIN, ['settings.edit']);
});

function useOllamaUrl(?string $url): void
{
    config(['settings.ai_ollama_base_url' => $url, 'ai.ollama.base_url' => null]);
}

test('a metadata or path-carrying base url is never requested', function (string $url) {
    useOllamaUrl($url);
    Http::fake();

    $seo = app(AiContentGeneratorService::class)->setProvider('ollama')->generateSeoMeta(['title' => 'x', 'content' => 'y']);

    Http::assertNothingSent();
    expect($seo['seo_title'] ?? '')->not->toContain('EXFILTRATED');
})->with([
    'aws metadata' => 'http://169.254.169.254/latest/meta-data/internal-secret',
    'metadata host' => 'http://169.254.169.254',
    'ipv4-mapped metadata' => 'http://[::ffff:169.254.169.254]',
    'gcp metadata name' => 'http://metadata.google.internal',
    'internal service path' => 'http://127.0.0.1:9321/internal/admin',
]);

test('generate-seo does not reflect an internal service through ollama', function () {
    useOllamaUrl('http://127.0.0.1:9321/internal');
    Http::fake(['*' => Http::response(['message' => ['content' => json_encode(['seo_title' => 'EXFILTRATED-VIA-SSRF'])]])]);

    $this->actingAs($this->editor)
        ->postJson('/admin/ai/generate-seo', ['title' => 'Gardening', 'content' => 'Tips', 'provider' => 'ollama'])
        ->assertOk()
        ->assertDontSee('EXFILTRATED-VIA-SSRF');

    Http::assertNothingSent();
});

test('generate-seo rejects a provider that is not configured', function () {
    useOllamaUrl(null);
    Http::fake();

    $this->actingAs($this->editor)
        ->postJson('/admin/ai/generate-seo', ['title' => 'Gardening', 'provider' => 'ollama'])
        ->assertStatus(422);

    Http::assertNothingSent();
});

test('a local ollama server still works, without following redirects', function () {
    useOllamaUrl('http://127.0.0.1:11434');
    Http::fake(['127.0.0.1:11434/api/chat' => Http::response([
        'message' => ['content' => json_encode(['seo_title' => 'Gardening tips for beginners'])],
    ])]);

    $this->actingAs($this->editor)
        ->postJson('/admin/ai/generate-seo', ['title' => 'Gardening', 'content' => 'Tips', 'provider' => 'ollama'])
        ->assertOk()
        ->assertJsonPath('data.seo_title', 'Gardening tips for beginners');

    Http::assertSent(fn (HttpRequest $request) => $request->url() === 'http://127.0.0.1:11434/api/chat');
});

test('ollama base urls are validated', function (string $url, bool $valid) {
    expect(app(OllamaUrlValidator::class)->isValid($url))->toBe($valid);
})->with([
    'localhost' => ['http://localhost:11434', true],
    'loopback with slash' => ['http://127.0.0.1:11434/', true],
    'private network' => ['http://10.0.4.12:11434', true],
    'https' => ['https://192.168.1.20', true],
    'link-local' => ['http://169.254.169.254', false],
    'zero network' => ['http://0.0.0.0:11434', false],
    'alibaba metadata' => ['http://100.100.100.200', false],
    'ipv6 link-local' => ['http://[fe80::1]:11434', false],
    'path' => ['http://localhost:11434/api', false],
    'query' => ['http://localhost:11434?x=1', false],
    'credentials' => ['http://user:pass@localhost:11434', false],
    'file scheme' => ['file:///etc/passwd', false],
    'gopher scheme' => ['gopher://localhost:11434', false],
    'not a url' => ['localhost:11434', false],
]);

test('private networks can be blocked for publicly hosted ollama', function () {
    config(['ai.ollama.allow_private_network' => false]);

    expect(app(OllamaUrlValidator::class)->isValid('http://127.0.0.1:11434'))->toBeFalse()
        ->and(app(OllamaUrlValidator::class)->isValid('http://10.0.0.5:11434'))->toBeFalse();
});

test('only a superadmin can change the ollama base url', function (string $via) {
    useOllamaUrl('http://localhost:11434');

    $response = saveOllamaSetting($this, $this->settingsAdmin, 'http://10.0.4.12:11434', $via);

    expect($via === 'api' ? $response->status() : session('errors')?->has('ai_ollama_base_url'))
        ->toBe($via === 'api' ? 422 : true);
    $this->assertDatabaseMissing('settings', ['option_name' => 'ai_ollama_base_url', 'option_value' => 'http://10.0.4.12:11434']);
})->with(['web', 'api']);

test('other admins can still save the ai settings form unchanged', function () {
    useOllamaUrl('http://localhost:11434');

    saveOllamaSetting($this, $this->settingsAdmin, 'http://localhost:11434', 'web')->assertSessionHasNoErrors();
});

test('a superadmin cannot save an unsafe ollama base url', function (string $via) {
    $response = saveOllamaSetting($this, $this->superadminUser, 'http://169.254.169.254/latest/meta-data', $via);

    if ($via === 'api') {
        $response->assertStatus(422);
    } else {
        $response->assertSessionHasErrors('ai_ollama_base_url');
    }
    $this->assertDatabaseMissing('settings', ['option_name' => 'ai_ollama_base_url', 'option_value' => 'http://169.254.169.254/latest/meta-data']);
})->with(['web', 'api']);

test('a superadmin can save a local ollama base url', function () {
    saveOllamaSetting($this, $this->superadminUser, 'http://127.0.0.1:11434', 'web')->assertSessionHasNoErrors();

    $this->assertDatabaseHas('settings', ['option_name' => 'ai_ollama_base_url', 'option_value' => 'http://127.0.0.1:11434']);
});

function saveOllamaSetting($test, User $user, string $url, string $via)
{
    if ($via === 'api') {
        return $test->actingAs($user, 'sanctum')
            ->putJson('/api/v1/settings', ['settings' => ['ai_ollama_base_url' => $url]]);
    }

    return $test->actingAs($user)
        ->from('/admin/settings?tab=integrations')
        ->post('/admin/settings', ['ai_ollama_base_url' => $url]);
}
