<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailConnection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmailConnectionService
{
    /**
     * Create a new email connection.
     */
    public function create(array $data): EmailConnection
    {
        return DB::transaction(function () use ($data) {
            $connection = new EmailConnection();
            $connection->fill($this->prepareData($data));
            $connection->created_by = Auth::id();
            $connection->save();

            // If this is set as default, unset other defaults
            if ($connection->is_default) {
                $this->clearOtherDefaults($connection);
            }

            return $connection;
        });
    }

    /**
     * Update an existing email connection.
     */
    public function update(EmailConnection $connection, array $data): EmailConnection
    {
        return DB::transaction(function () use ($connection, $data) {
            $data = $this->mergeCredentials($connection, $data);
            $preparedData = $this->prepareData($data);

            $connection->fill($preparedData);
            $connection->updated_by = Auth::id();
            $connection->save();

            // If this is set as default, unset other defaults
            if ($connection->is_default) {
                $this->clearOtherDefaults($connection);
            }

            return $connection;
        });
    }

    /**
     * Merge new credentials with existing ones.
     * Preserves existing values when new values are empty or masked (********).
     */
    protected function mergeCredentials(EmailConnection $connection, array $data): array
    {
        $existingCredentials = $connection->credentials ?? [];
        $newCredentials = $data['credentials'] ?? [];

        // Merge: keep existing value if new value is empty or masked
        $mergedCredentials = $existingCredentials;
        foreach ($newCredentials as $key => $value) {
            // Only update if new value is not empty and not a masked password
            if (! empty($value) && $value !== '********') {
                $mergedCredentials[$key] = $value;
            }
        }

        $data['credentials'] = $mergedCredentials;

        return $data;
    }

    /**
     * Delete an email connection.
     */
    public function delete(EmailConnection $connection): bool
    {
        return (bool) $connection->delete();
    }

    /**
     * Get all active connections ordered by priority.
     */
    public function getActiveConnections(): Collection
    {
        return EmailConnection::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get the default connection.
     */
    public function getDefaultConnection(): ?EmailConnection
    {
        return EmailConnection::query()
            ->active()
            ->default()
            ->first();
    }

    /**
     * Get the best available connection (default or first active by priority).
     */
    public function getBestConnection(): ?EmailConnection
    {
        $default = $this->getDefaultConnection();
        if ($default) {
            return $default;
        }

        return EmailConnection::query()
            ->active()
            ->ordered()
            ->first();
    }

    /**
     * Set a connection as the default.
     */
    public function setDefault(EmailConnection $connection): void
    {
        DB::transaction(function () use ($connection) {
            // Unset all other defaults
            EmailConnection::query()
                ->where('id', '!=', $connection->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this one as default
            $connection->update([
                'is_default' => true,
                'updated_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Reorder connections by priority.
     *
     * @param  array  $orderedIds  Array of connection IDs in desired order
     */
    public function reorderPriorities(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                EmailConnection::where('id', $id)->update([
                    'priority' => ($index + 1) * 10,
                    'updated_by' => Auth::id(),
                ]);
            }
        });
    }

    /**
     * Test a connection and update its status.
     */
    public function testConnection(EmailConnection $connection, string $testEmail): array
    {
        $provider = EmailProviderRegistry::getProvider($connection->provider_type);

        if (! $provider) {
            $result = [
                'success' => false,
                'message' => __('Unknown provider type: :type', ['type' => $connection->provider_type]),
            ];
            $connection->markAsTested(false, $result['message']);

            return $result;
        }

        $result = $provider->testConnection($connection, $testEmail);
        $connection->markAsTested($result['success'], $result['message']);

        return $result;
    }

    /**
     * Prepare data for create/update.
     */
    protected function prepareData(array $data): array
    {
        $prepared = [
            'name' => $data['name'] ?? '',
            'from_email' => $data['from_email'] ?? '',
            'from_name' => $data['from_name'] ?? null,
            'force_from_email' => $data['force_from_email'] ?? false,
            'force_from_name' => $data['force_from_name'] ?? false,
            'provider_type' => $data['provider_type'] ?? '',
            'settings' => $data['settings'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $data['is_default'] ?? false,
            'priority' => $data['priority'] ?? 10,
        ];

        // Handle credentials separately (they should be encrypted)
        if (isset($data['credentials'])) {
            $prepared['credentials'] = array_filter($data['credentials'], fn ($v) => ! empty($v));
        }

        return $prepared;
    }

    /**
     * Clear default flag from other connections.
     */
    protected function clearOtherDefaults(EmailConnection $connection): void
    {
        EmailConnection::query()
            ->where('id', '!=', $connection->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
