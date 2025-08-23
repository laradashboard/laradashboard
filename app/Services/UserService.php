<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class UserService
{
    public function getUsers(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::applyFilters($filters);

        return $query->paginateData([
            'per_page' => $filters['per_page'] ?? config('settings.default_pagination') ?? 10,
        ]);
    }

    public function createUser(array $data): User
    {
        $userData = $this->prepareUserData($data, true);
        $user = User::create($userData);

        $this->syncUserRoles($user, $data);

        return $user;
    }

    public function getUserById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function updateUser(User $user, array $data): User
    {
        $updateData = $this->prepareUserData($data, false);
        $user->update($updateData);

        $this->syncUserRoles($user, $data);

        return $user->refresh();
    }

    public function createUserWithRelations(array $data): User
    {
        $user = $this->createUser($data);
        return $user->load('roles');
    }

    public function updateUserWithRelations(User $user, array $data): User
    {
        $updatedUser = $this->updateUser($user, $data);
        return $updatedUser->load('roles');
    }

    public function createUserWithMetadata(array $data, $request = null): User
    {
        return DB::transaction(function () use ($data, $request) {
            $userData = $this->prepareUserDataWithAvatar($data, true);
            $user = new User($userData);

            $user = $this->applyHooks($user, $request, 'user_store_before_save');
            $user->save();
            $user = $this->applyHooks($user, $request, 'user_store_after_save');

            $this->handleUserMetadata($user, $data, 'create', $request);
            $this->handleUserRoles($user, $data);

            return $user;
        });
    }

    public function updateUserWithMetadata(User $user, array $data, $request = null): User
    {
        return DB::transaction(function () use ($user, $data, $request) {
            $userData = $this->prepareUserDataWithAvatar($data, false, $user);
            $this->updateUserAttributes($user, $userData);

            $user = $this->applyHooks($user, $request, 'user_update_before_save');
            $user->save();
            $user = $this->applyHooks($user, $request, 'user_update_after_save');

            $this->handleUserMetadata($user, $data, 'update', $request);
            $this->handleUserRoles($user, $data, 'update');

            return $user;
        });
    }

    private function prepareUserData(array $data, bool $isCreate = true): array
    {
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
        ];

        if ($isCreate || $this->shouldUpdatePassword($data)) {
            $userData['password'] = Hash::make($data['password']);
        }

        return $userData;
    }

    private function prepareUserDataWithAvatar(array $data, bool $isCreate = true, ?User $existingUser = null): array
    {
        $userData = $this->prepareUserData($data, $isCreate);
        $userData['avatar_id'] = $data['avatar_id'] ?? ($existingUser?->avatar_id);

        return $userData;
    }

    private function shouldUpdatePassword(array $data): bool
    {
        return isset($data['password']) && ! empty($data['password']);
    }

    private function updateUserAttributes(User $user, array $userData): void
    {
        foreach ($userData as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }
    }

    private function applyHooks(User $user, $request, string $hookName): User
    {
        if ($request && function_exists('ld_apply_filters')) {
            return ld_apply_filters($hookName, $user, $request) ?: $user;
        }

        return $user;
    }

    private function syncUserRoles(User $user, array $data): void
    {
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
    }

    private function getUserMetadataFieldGroups(): array
    {
        return [
            'profile' => ['display_name', 'bio', 'timezone', 'locale'],
            'social' => ['social_facebook', 'social_x', 'social_youtube', 'social_linkedin', 'social_website'],
        ];
    }

    private function shouldProcessMetadataField(string $field, array $data): bool
    {
        return array_key_exists($field, $data) && ! empty($data[$field]);
    }

    private function getMetadataFieldValueForUpdate(string $field, array $data, $request = null): ?string
    {
        // Priority: request object, then validated data
        if ($request && $request->has($field)) {
            return $request->input($field, '');
        }

        if (! $request && array_key_exists($field, $data)) {
            return $data[$field] ?? '';
        }

        return null; // Field not provided
    }

    private function handleUserRoles(User $user, array $data, string $operation = 'create'): void
    {
        if (! isset($data['roles'])) {
            return;
        }

        match ($operation) {
            'create' => $this->assignUserRoles($user, $data['roles']),
            'update' => $this->updateUserRoles($user, $data['roles']),
            default => throw new InvalidArgumentException("Unsupported operation: {$operation}")
        };
    }

    private function assignUserRoles(User $user, array $roles): void
    {
        if (! $roles) {
            return;
        }

        $filteredRoles = array_filter($roles);

        if (! empty($filteredRoles)) {
            $user->syncRoles($filteredRoles);
        }
    }

    private function updateUserRoles(User $user, array $roles): void
    {
        $user->roles()->detach();
        $this->assignUserRoles($user, $roles);
    }

    private function handleUserMetadata(User $user, array $data, string $operation = 'create', $request = null): void
    {
        $allFields = collect($this->getUserMetadataFieldGroups())->flatten();

        $metadataToProcess = $allFields
            ->map(fn ($field) => $this->prepareMetadataRecord($user, $field, $data, $operation, $request))
            ->filter()
            ->values();

        if ($metadataToProcess->isEmpty()) {
            return;
        }

        match ($operation) {
            'create' => $this->bulkCreateMetadata($user, $metadataToProcess),
            'update' => $this->bulkUpdateMetadata($user, $metadataToProcess),
            default => throw new InvalidArgumentException("Unsupported operation: {$operation}")
        };
    }

    private function prepareMetadataRecord(User $user, string $field, array $data, string $operation, $request = null): ?array
    {
        if ($operation === 'create' && ! $this->shouldProcessMetadataField($field, $data)) {
            return null;
        }

        if ($operation === 'update') {
            $value = $this->getMetadataFieldValueForUpdate($field, $data, $request);
            if ($value === null) {
                return null;
            }
            $data[$field] = $value;
        }

        return [
            'user_id' => $user->id,
            'meta_key' => $field,
            'meta_value' => $data[$field],
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function bulkCreateMetadata(User $user, Collection $metadataRecords): void
    {
        $user->userMeta()->insert($metadataRecords->toArray());
    }

    private function bulkUpdateMetadata(User $user, Collection $metadataRecords): void
    {
        $user->userMeta()->upsert(
            $metadataRecords->toArray(),
            ['user_id', 'meta_key'], // Unique columns
            ['meta_value', 'type', 'updated_at'] // Columns to update
        );
    }
}
