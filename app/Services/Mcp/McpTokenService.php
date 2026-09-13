<?php

declare(strict_types=1);

namespace App\Services\Mcp;

use App\Enums\Hooks\McpFilterHook;
use App\Models\User;
use App\Support\Facades\Hook;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\PersonalAccessToken;

class McpTokenService
{
    public const TOKEN_NAME = 'laradashboard-mcp';

    public function createToken(User $user, ?string $label = null): string
    {
        return $this->issueToken($user, $label)['plain_text'];
    }

    /**
     * @return array{plain_text: string, token: PersonalAccessToken}
     */
    public function issueToken(User $user, ?string $label = null): array
    {
        $abilities = $this->resolveAbilitiesForUser($user);
        $newToken = $user->createToken($this->formatTokenName($label), $abilities);

        return [
            'plain_text' => $newToken->plainTextToken,
            'token' => $newToken->accessToken,
        ];
    }

    /**
     * @return Collection<int, PersonalAccessToken>
     */
    public function listTokensForUser(User $user): Collection
    {
        return $this->mcpTokensQuery($user)
            ->orderByDesc('created_at')
            ->get();
    }

    public function revokeToken(User $user, int $tokenId): bool
    {
        return $this->mcpTokensQuery($user)
            ->whereKey($tokenId)
            ->delete() > 0;
    }

    public function tokenLabel(PersonalAccessToken $token): string
    {
        $prefix = self::TOKEN_NAME.': ';

        if (str_starts_with($token->name, $prefix)) {
            return substr($token->name, strlen($prefix));
        }

        if ($token->name === self::TOKEN_NAME) {
            return __('MCP Agent');
        }

        return $token->name;
    }

    public function formatTokenName(?string $label): string
    {
        $label = trim((string) $label);

        if ($label === '') {
            return self::TOKEN_NAME;
        }

        return self::TOKEN_NAME.': '.$label;
    }

    /**
     * @return array<int, string>
     */
    public function resolveAbilitiesForUser(User $user): array
    {
        $abilities = ['mcp:access'];

        /** @var array<string, string> $map */
        $map = Hook::applyFilters(McpFilterHook::ABILITY_PERMISSION_MAP, [
            'mcp:posts.read' => 'post.view',
            'mcp:posts.write' => 'post.create',
            'mcp:posts.update' => 'post.edit',
            'mcp:posts.delete' => 'post.delete',
            'mcp:terms.read' => 'term.view',
            'mcp:media.read' => 'media.view',
            'mcp:media.write' => 'media.create',
            'mcp:email_templates.read' => 'email_template.view',
            'mcp:email.send' => 'email_template.view',
            'mcp:briefing.read' => 'dashboard.view',
            'mcp:ops.cache' => 'settings.edit',
            'mcp:ops.logs.read' => 'settings.edit',
            'mcp:ops.health.read' => 'dashboard.view',
            'mcp:modules.read' => 'module.view',
            'mcp:modules.activate' => 'module.activate',
            'mcp:modules.deactivate' => 'module.deactivate',
        ]);

        foreach ($map as $ability => $permission) {
            if ($user->can($permission)) {
                $abilities[] = $ability;
            }
        }

        /** @var array<int, string> $abilities */
        $abilities = Hook::applyFilters(McpFilterHook::TOKEN_ABILITIES, $abilities, $user);

        return array_values(array_unique($abilities));
    }

    public function isMcpToken(?HasAbilities $token): bool
    {
        if (! $token instanceof PersonalAccessToken) {
            return false;
        }

        return $token->name === self::TOKEN_NAME
            || str_starts_with($token->name, self::TOKEN_NAME.': ');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Relations\MorphMany<PersonalAccessToken, User>  $relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<PersonalAccessToken, User>
     */
    protected function mcpTokensQuery(User $user)
    {
        return $user->tokens()->where(function ($query): void {
            $query->where('name', self::TOKEN_NAME)
                ->orWhere('name', 'like', self::TOKEN_NAME.': %');
        });
    }
}
