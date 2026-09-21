<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mcp\StoreMcpMediaUploadRequest;
use App\Services\MediaLibraryService;
use App\Services\Mcp\McpMediaUploadService;
use App\Services\Mcp\McpTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class McpMediaUploadController extends Controller
{
    public function __construct(
        protected MediaLibraryService $mediaLibraryService,
        protected McpMediaUploadService $mcpMediaUploadService,
        protected McpTokenService $mcpTokenService,
    ) {
    }

    public function store(StoreMcpMediaUploadRequest $request): JsonResponse
    {
        $user = $this->authorizeMcpMediaWrite();

        return $this->processUpload($request, $user->id);
    }

    public function storeSigned(StoreMcpMediaUploadRequest $request, string $uploadToken): JsonResponse
    {
        $pending = $this->mcpMediaUploadService->pullPendingUpload($uploadToken);

        return $this->processUpload($request, (int) $pending['user_id'], $uploadToken, $pending);
    }

    /**
     * @param  array{title?: string|null, alt_text?: string|null}|null  $pending
     */
    protected function processUpload(
        StoreMcpMediaUploadRequest $request,
        int $userId,
        ?string $uploadToken = null,
        ?array $pending = null,
    ): JsonResponse {
        $authenticatedUser = Auth::id();

        if ($authenticatedUser !== null && (int) $authenticatedUser !== $userId) {
            abort(403, __('Upload session does not belong to this agent.'));
        }

        $tokenFromRequest = $uploadToken ?? $request->validated('upload_token');

        if ($pending === null && $tokenFromRequest !== null) {
            $pending = $this->mcpMediaUploadService->pullPendingUpload((string) $tokenFromRequest, $userId);
        }

        try {
            $media = $this->mediaLibraryService->uploadStandaloneFile(
                $request->file('file'),
                $pending['title'] ?? null,
                $pending['alt_text'] ?? null,
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $formatted = $this->mediaLibraryService->formatMediaForMcp($media);

        if ($tokenFromRequest !== null) {
            $this->mcpMediaUploadService->markUploadCompleted((string) $tokenFromRequest, $media->id);
        }

        $status = $formatted['serve_ok'] ? 201 : 422;

        return response()->json([
            'message' => $formatted['serve_ok']
                ? __('Media uploaded successfully.')
                : __('Media was stored but is not publicly serveable yet.'),
            'media' => $formatted,
        ], $status);
    }

    protected function authorizeMcpMediaWrite(): \App\Models\User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user === null) {
            abort(401, __('MCP authentication required.'));
        }

        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token === null || ! $this->mcpTokenService->isMcpToken($token)) {
            abort(403, __('A LaraDashboard MCP agent token is required.'));
        }

        if (! $token->can('mcp:media.write')) {
            abort(403, __('This token does not have the [mcp:media.write] ability.'));
        }

        if (! $user->can('media.create')) {
            abort(403, __('You do not have permission to perform this action.'));
        }

        return $user;
    }
}
