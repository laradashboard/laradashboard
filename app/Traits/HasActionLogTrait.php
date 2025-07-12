<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\ActionType;
use App\Models\ActionLog;
use Auth;

trait HasActionLogTrait
{
    /**
     * Log an action with a message, model, and optional additional data.
     *
     * @param string $message The action message (e.g., 'Post Created')
     * @param mixed $model The model being acted upon, or null
     * @param array $additionalData Additional data to log
     * @return ActionLog|null
     */
    public function logAction(string $message, $model = null, array $additionalData = []): ?ActionLog
    {
        // Determine the action type based on the message
        $actionType = ActionType::CREATED; // Default

        if (str_contains(strtolower($message), 'created')) {
            $actionType = ActionType::CREATED;
        } elseif (str_contains(strtolower($message), 'updated')) {
            $actionType = ActionType::UPDATED;
        } elseif (str_contains(strtolower($message), 'deleted')) {
            if (str_contains(strtolower($message), 'bulk')) {
                $actionType = ActionType::BULK_DELETED;
            } else {
                $actionType = ActionType::DELETED;
            }
        } elseif (str_contains(strtolower($message), 'viewed')) {
            $actionType = ActionType::VIEWED;
        } elseif (str_contains(strtolower($message), 'approved')) {
            $actionType = ActionType::APPROVED;
        } elseif (str_contains(strtolower($message), 'rejected')) {
            $actionType = ActionType::REJECTED;
        } elseif (str_contains(strtolower($message), 'exception')) {
            $actionType = ActionType::EXCEPTION;
        }

        // Prepare the data
        $data = [];

        // If a model is provided, convert it to an array
        if ($model !== null) {
            $modelType = strtolower(class_basename($model));
            $data[$modelType] = $model;
        }

        // Merge with additional data if provided
        if (! empty($additionalData)) {
            $data = array_merge($data, $additionalData);
        }

        // If data is still empty, use a default
        if (empty($data)) {
            $data = ['action' => $message];
        }

        // Call the storeActionLog method with the determined action type and data
        return $this->storeActionLog($actionType, $data, $message);
    }

    public function storeActionLog(ActionType $type, array $data, ?string $title = null): ?ActionLog
    {
        try {
            if (! $title) {
                $dataKey = key($data); // Get the first key of the data array
                $name = Auth::user()->username ?? 'Unknown'; // Get the authenticated user's username, fallback to 'Unknown'
                $title = ucfirst($dataKey).' '.$type->value.' by '.$name;
            }

            $actionLog = ActionLog::create([
                'type' => $type->value,
                'title' => $title,
                'action_by' => auth()->id(), // Store the user's ID who triggered the action
                'data' => json_encode($data), // Store the action data as JSON
            ]);

            return $actionLog;
        } catch (\Exception $e) {
            return null;
        }
    }
}
