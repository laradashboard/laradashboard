<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class EmailTemplateVariableService
{
    /**
     * Get common email template variables from settings
     */
    public function getCommonVariables(): array
    {
        return Cache::remember('email_template_common_variables', 3600, function () {
            $settings = Setting::whereIn('option_name', [
                'site_logo',
                'site_logo_lite',
                'site_logo_dark',
                'company_name',
                'primary_color',
                'secondary_color',
                'body_bg_color',
                'app_name',
            ])->pluck('option_value', 'option_name')->toArray();

            return [
                'site_logo' => $this->getLogoUrl($settings['site_logo_lite'] ?? $settings['site_logo'] ?? null),
                'site_logo_dark' => $this->getLogoUrl($settings['site_logo_dark'] ?? null),
                'company' => $settings['company_name'] ?? $settings['app_name'] ?? config('app.name', 'Company'),
                'primary_color' => $settings['primary_color'] ?? '#635bff',
                'secondary_color' => $settings['secondary_color'] ?? '#6c757d',
                'body_bg_color' => $settings['body_bg_color'] ?? '#f5f5f5',
                'app_name' => $settings['app_name'] ?? config('app.name', 'Application'),
            ];
        });
    }

    /**
     * Get logo URL with fallback
     */
    private function getLogoUrl(?string $logoPath): string
    {
        if (empty($logoPath)) {
            return asset('images/logo/lara-dashboard.png');
        }

        // If it's already a full URL, return as is
        if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
            return $logoPath;
        }

        // If it starts with uploads/, add asset() wrapper
        if (str_starts_with($logoPath, 'uploads/')) {
            return asset($logoPath);
        }

        // If it starts with /, it's already a path from root
        if (str_starts_with($logoPath, '/')) {
            return asset(ltrim($logoPath, '/'));
        }

        // Default fallback
        return asset('images/logo/lara-dashboard.png');
    }

    /**
     * Process assignees for email templates
     */
    public function processAssignees($assignees): string
    {
        if (empty($assignees)) {
            return 'Not assigned';
        }

        if (is_string($assignees)) {
            return $assignees ?: 'Not assigned';
        }

        if (is_array($assignees)) {
            // If it's an array of user IDs, get user names
            if (! empty($assignees) && isset($assignees[0]) && is_numeric($assignees[0])) {
                $users = \App\Models\User::whereIn('id', $assignees)->pluck('name')->toArray();
                return ! empty($users) ? implode(', ', $users) : 'Not assigned';
            }

            // If it's an array of names
            $filtered = array_filter($assignees);
            return ! empty($filtered) ? implode(', ', $filtered) : 'Not assigned';
        }

        // Handle collection or other objects
        if (method_exists($assignees, 'pluck')) {
            $names = $assignees->pluck('name')->filter()->toArray();
            return ! empty($names) ? implode(', ', $names) : 'Not assigned';
        }

        return 'Multiple assignees';
    }

    /**
     * Merge activity data with common variables and ensure no empty values
     */
    public function mergeActivityVariables(array $activityData, array $additionalData = []): array
    {
        $commonVars = $this->getCommonVariables();

        // Process assigned_to field - check multiple possible keys
        $assignees = $activityData['assigned_to'] ?? $activityData['users'] ?? $activityData['assignedUsers'] ?? null;
        if ($assignees) {
            $activityData['assigned_to'] = $this->processAssignees($assignees);
        }

        $merged = array_merge($commonVars, $activityData, $additionalData);

        // Ensure no empty values with fallbacks
        return $this->ensureNoEmptyValues($merged);
    }

    /**
     * Ensure no variables are empty by providing fallbacks
     */
    private function ensureNoEmptyValues(array $variables): array
    {
        $fallbacks = [
            'activity_title' => 'Activity',
            'activity_type' => 'Activity',
            'activity_description' => 'No description provided',
            'activity_status' => 'Pending',
            'contact_name' => 'N/A',
            'deal_title' => 'N/A',
            'assigned_to' => 'Not assigned',
            'created_by' => 'System',
            'updated_by' => 'System',
            'deleted_by' => 'System',
            'created_date' => 'N/A',
            'updated_date' => 'N/A',
            'deleted_date' => 'N/A',
            'due_date' => 'No due date',
            'activity_date' => 'N/A',
            'deletion_reason' => 'No reason provided',
            'update_summary' => 'Activity has been updated',
            'first_name' => 'User',
            'recipient_name' => 'User',
            'recipient_email' => '',
            'activity_url' => '#',
        ];

        foreach ($fallbacks as $key => $fallback) {
            if (! isset($variables[$key]) || empty($variables[$key]) || $variables[$key] === null) {
                $variables[$key] = $fallback;
            }
        }

        return $variables;
    }

    /**
     * Get activity type label from activity data
     */
    public function getActivityTypeLabel(array $activityData): string
    {
        // Check if it's a contact activity
        if (isset($activityData['type_label']) && ! empty($activityData['type_label'])) {
            return $activityData['type_label'];
        }

        // Check if it's a deal activity
        if (isset($activityData['type']) && ! empty($activityData['type'])) {
            return ucfirst($activityData['type']);
        }

        // Check for enum type
        if (isset($activityData['type']) && is_object($activityData['type'])) {
            return ucfirst($activityData['type']->value ?? 'Activity');
        }

        // Default fallback
        return 'Activity';
    }

    /**
     * Clear the cache for common variables
     */
    public function clearCache(): void
    {
        Cache::forget('email_template_common_variables');
    }
}
