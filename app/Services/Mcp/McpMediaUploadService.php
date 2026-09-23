<?php

declare(strict_types=1);

namespace App\Services\Mcp;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class McpMediaUploadService
{
    public const PENDING_CACHE_PREFIX = 'mcp_media_upload:pending:';

    public const COMPLETED_CACHE_PREFIX = 'mcp_media_upload:completed:';

    public const TTL_MINUTES = 15;

    /**
     * @return array{
     *     upload_token: string,
     *     upload_url: string,
     *     upload_url_bearer: string,
     *     upload_method: string,
     *     upload_field: string,
     *     expires_at: string,
     *     max_bytes: int
     * }
     */
    public function createPendingUpload(User $user, array $attributes): array
    {
        $filename = (string) ($attributes['filename'] ?? '');
        $mimeType = strtolower(trim((string) ($attributes['mime_type'] ?? '')));

        if ($filename === '' || $mimeType === '') {
            throw ValidationException::withMessages([
                'filename' => [__('A valid filename and MIME type are required.')],
            ]);
        }

        $token = (string) Str::uuid();
        $expiresAt = now()->addMinutes(self::TTL_MINUTES);

        Cache::put(self::PENDING_CACHE_PREFIX.$token, [
            'user_id' => $user->id,
            'filename' => $filename,
            'mime_type' => $mimeType,
            'title' => $attributes['title'] ?? null,
            'alt_text' => $attributes['alt_text'] ?? null,
        ], $expiresAt);

        return [
            'upload_token' => $token,
            'upload_url' => URL::temporarySignedRoute(
                'mcp.media.upload.signed',
                $expiresAt,
                ['uploadToken' => $token]
            ),
            'upload_url_bearer' => route('mcp.media.upload.store'),
            'upload_method' => 'POST',
            'upload_field' => 'file',
            'expires_at' => $expiresAt->toIso8601String(),
            'max_bytes' => \App\Services\MediaLibraryService::MCP_MAX_UPLOAD_BYTES,
        ];
    }

    /**
     * @return array{user_id: int, filename: string, mime_type: string, title: string|null, alt_text: string|null}
     */
    public function pullPendingUpload(string $uploadToken, ?int $expectedUserId = null): array
    {
        /** @var array<string, mixed>|null $pending */
        $pending = Cache::get(self::PENDING_CACHE_PREFIX.$uploadToken);

        if (! is_array($pending) || ! isset($pending['user_id'], $pending['filename'], $pending['mime_type'])) {
            throw ValidationException::withMessages([
                'upload_token' => [__('Upload session not found or expired.')],
            ]);
        }

        if ($expectedUserId !== null && (int) $pending['user_id'] !== $expectedUserId) {
            throw ValidationException::withMessages([
                'upload_token' => [__('Upload session does not belong to this agent.')],
            ]);
        }

        return [
            'user_id' => (int) $pending['user_id'],
            'filename' => (string) $pending['filename'],
            'mime_type' => (string) $pending['mime_type'],
            'title' => isset($pending['title']) ? (string) $pending['title'] : null,
            'alt_text' => isset($pending['alt_text']) ? (string) $pending['alt_text'] : null,
        ];
    }

    public function markUploadCompleted(string $uploadToken, int $mediaId): void
    {
        Cache::forget(self::PENDING_CACHE_PREFIX.$uploadToken);
        Cache::put(
            self::COMPLETED_CACHE_PREFIX.$uploadToken,
            ['media_id' => $mediaId],
            now()->addMinutes(self::TTL_MINUTES)
        );
    }

    public function resolveCompletedMediaId(string $uploadToken): int
    {
        /** @var array{media_id?: int}|null $completed */
        $completed = Cache::get(self::COMPLETED_CACHE_PREFIX.$uploadToken);

        if (! is_array($completed) || ! isset($completed['media_id'])) {
            throw ValidationException::withMessages([
                'upload_token' => [__('No completed upload was found for this token. POST the file to upload_url first.')],
            ]);
        }

        return (int) $completed['media_id'];
    }
}
