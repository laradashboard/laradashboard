<?php

namespace App\Services;

class EmailTemplateEngine
{
    /**
     * Available template variables with their descriptions
     */
    public static function getAvailableVariables(): array
    {
        return [
            'user' => [
                '{first_name} or {{first_name}}' => 'User\'s first name',
                '{last_name} or {{last_name}}' => 'User\'s last name',
                '{full_name} or {{full_name}}' => 'User\'s full name',
                '{email} or {{email}}' => 'User\'s email address',
            ],
            'system' => [
                '{company_name} or {{company_name}}' => 'Your company name',
                '{company_website} or {{company_website}}' => 'Your company website',
                '{current_year} or {{current_year}}' => 'Current year (for copyright)',
                '{current_date} or {{current_date}}' => 'Current date',
                '{current_time} or {{current_time}}' => 'Current date and time',
            ],
        ];
    }

    /**
     * Preview template with sample data
     */
    public function preview(string $content, array $sampleData = []): string
    {
        $defaultSampleData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'company_name' => config('app.name', 'Your Company'),
            'company_website' => config('app.url', 'https://yourwebsite.com'),
            'current_year' => now()->year,
            'current_date' => now()->format('F j, Y'),
            'current_time' => now()->format('F j, Y \a\t g:i A'),
        ];

        $variables = array_merge($defaultSampleData, $sampleData);

        return $this->replaceVariables($content, $variables);
    }

    /**
     * Render template with actual data
     */
    public function render(string $content, $contact = null, $campaign = null, string $recipientUuid = null): string
    {
        $variables = [
            'first_name' => $contact->first_name ?? '',
            'last_name' => $contact->last_name ?? '',
            'full_name' => $contact->full_name ?? ($contact->first_name . ' ' . $contact->last_name) ?? '',
            'email' => $contact->email ?? '',
            'company' => config('app.name', 'Your Company'),
            'company_name' => config('app.name', 'Your Company'),
            'company_website' => config('app.url', 'https://yourwebsite.com'),
            'current_year' => now()->year,
            'current_date' => now()->format('F j, Y'),
            'current_time' => now()->format('F j, Y \a\t g:i A'),
        ];

        return $this->replaceVariables($content, $variables);
    }

    /**
     * Replace variables in content
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $cleanValue = trim((string) $value);
            // Replace double brackets first to avoid conflicts
            $content = str_replace('{{' . $key . '}}', $cleanValue, $content);
            $content = str_replace('{' . $key . '}', $cleanValue, $content);
        }

        return $content;
    }
}
