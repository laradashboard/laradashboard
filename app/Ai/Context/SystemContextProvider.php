<?php

declare(strict_types=1);

namespace App\Ai\Context;

use App\Ai\Contracts\AiContextProviderInterface;

/**
 * Provides general system context to AI.
 */
class SystemContextProvider implements AiContextProviderInterface
{
    public function key(): string
    {
        return 'system';
    }

    public function context(): array
    {
        return [
            'app_name' => config('app.name'),
            'current_user' => $this->getCurrentUserContext(),
            'current_date' => now()->format('Y-m-d'),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
        ];
    }

    private function getCurrentUserContext(): ?array
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->full_name ?? '',
            'email' => $user->email,
        ];
    }
}
