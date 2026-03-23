<?php

declare(strict_types=1);

namespace App\View\Concerns;

use App\Enums\Hooks\FrontendFilterHook;
use App\Support\Facades\Hook;

/** @phpstan-ignore trait.unused */
trait HasSocialLinks
{
    /**
     * Platform icon mapping.
     */
    protected static array $platformIcons = [
        'twitter' => 'mdi:twitter',
        'facebook' => 'mdi:facebook',
        'instagram' => 'mdi:instagram',
        'github' => 'mdi:github',
        'linkedin' => 'mdi:linkedin',
        'youtube' => 'mdi:youtube',
        'tiktok' => 'mdi:music-note',
    ];

    /**
     * Load social links from settings with platform icon mapping.
     */
    protected function loadSocialLinks(): array
    {
        $settingsSocial = config('settings.social_links');

        if (! empty($settingsSocial) && is_array($settingsSocial)) {
            $links = [];

            foreach ($settingsSocial as $platform => $url) {
                if (! empty($url)) {
                    $links[] = [
                        'icon' => static::$platformIcons[$platform] ?? 'mdi:link',
                        'url' => $url,
                        'label' => ucfirst($platform),
                    ];
                }
            }

            if (! empty($links)) {
                $filtered = Hook::applyFilters(FrontendFilterHook::SOCIAL_LINKS, $links);

                return is_array($filtered) ? $filtered : $links;
            }
        }

        // Default social links
        $defaultSocialLinks = [
            ['icon' => 'mdi:twitter', 'url' => '#', 'label' => 'Twitter'],
            ['icon' => 'mdi:facebook', 'url' => '#', 'label' => 'Facebook'],
            ['icon' => 'mdi:instagram', 'url' => '#', 'label' => 'Instagram'],
            ['icon' => 'mdi:github', 'url' => '#', 'label' => 'GitHub'],
        ];

        $filtered = Hook::applyFilters(FrontendFilterHook::SOCIAL_LINKS, $defaultSocialLinks);

        return is_array($filtered) ? $filtered : $defaultSocialLinks;
    }
}
