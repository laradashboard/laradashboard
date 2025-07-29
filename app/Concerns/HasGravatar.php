<?php

declare(strict_types=1);

namespace App\Concerns;

trait HasGravatar
{
    /**
     * Get the Gravatar URL for the model's email.
     */
    public function getGravatarUrl(int $size = 80): string
    {
        $hash = md5(strtolower(trim($this->email)));

        // Fallback with UI Avatars if not gravatar is set.
        $uiAvatarUrl = urlencode("https://ui-avatars.com/api/{$this->name}/{$size}/635bff/fff/2");

        return "https://www.gravatar.com/avatar/{$hash}?d={$uiAvatarUrl}";
    }
}
