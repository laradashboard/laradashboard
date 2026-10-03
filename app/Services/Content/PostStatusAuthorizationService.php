<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PostStatusAuthorizationService
{
    /**
     * @var list<string>
     */
    private const PUBLISHER_STATUSES = [
        PostStatus::PUBLISHED->value,
        PostStatus::SCHEDULED->value,
        PostStatus::PRIVATE->value,
    ];

    /**
     * Resolve the post status that may be persisted for the given actor.
     *
     * Users without {@see PostPolicy::publish} cannot move content into publisher-only
     * states or unpublish live content; escalation attempts become pending review.
     */
    public function resolveStatus(
        User $user,
        ?Post $existingPost,
        string $requestedStatus,
        bool $scheduleRequested = false,
    ): string {
        $targetStatus = $scheduleRequested
            ? PostStatus::SCHEDULED->value
            : $requestedStatus;

        $canPublish = $this->userCanPublish($user, $existingPost);

        if ($canPublish) {
            if ($this->requiresPublishAuthorization($existingPost?->status, $targetStatus)) {
                $postForAuthorization = $existingPost ?? new Post(['user_id' => $user->id]);
                Gate::forUser($user)->authorize('publish', $postForAuthorization);
            }

            return $targetStatus;
        }

        $currentStatus = $existingPost === null
            ? PostStatus::DRAFT->value
            : ($existingPost->status ?? PostStatus::DRAFT->value);

        if ($this->isPublisherStatus($currentStatus) && ! $this->isPublisherStatus($targetStatus)) {
            return $currentStatus;
        }

        if ($this->isPublisherStatus($targetStatus) || $scheduleRequested) {
            return PostStatus::PENDING->value;
        }

        return $targetStatus;
    }

    private function userCanPublish(User $user, ?Post $existingPost): bool
    {
        $post = $existingPost ?? new Post(['user_id' => $user->id]);

        return Gate::forUser($user)->check('publish', $post);
    }

    private function requiresPublishAuthorization(?string $currentStatus, string $targetStatus): bool
    {
        $currentStatus ??= PostStatus::DRAFT->value;

        if ($currentStatus === $targetStatus) {
            return false;
        }

        if ($this->isPublisherStatus($targetStatus) && ! $this->isPublisherStatus($currentStatus)) {
            return true;
        }

        if ($this->isPublisherStatus($currentStatus) && ! $this->isPublisherStatus($targetStatus)) {
            return true;
        }

        return false;
    }

    private function isPublisherStatus(string $status): bool
    {
        return in_array($status, self::PUBLISHER_STATUSES, true);
    }
}
