<?php

namespace App\Contacts;

interface MediaInterface extends HasMediaInterface
{
    /**
     * Get the URL of the media in the specified collection
     */
    public function getMediaUrl(string $collection = 'default', string $conversion = ''): ?string;

    /**
     * Get all media URLs for a collection
     */
    public function getAllMediaUrls(string $collection = 'default'): array;
}
