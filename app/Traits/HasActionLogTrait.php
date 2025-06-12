<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\ActionType;
use App\Models\ActionLog;
use Auth;

trait HasActionLogTrait
{
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
