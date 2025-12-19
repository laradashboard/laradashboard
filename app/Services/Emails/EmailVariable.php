<?php

declare(strict_types=1);

namespace App\Services\Emails;

use App\Support\Facades\Hook;

class EmailVariable
{
    public function getAllVariablesData(): array
    {
        return Hook::applyFilters('email_template_variables_data', [
            'first_name' => [
                'label' => __('Recipient\'s first name'),
                'sample_data' => 'John',
                'replacement' => '', // should be filled dynamically.
            ],
            'last_name' => [
                'label' => __('Recipient\'s last name'),
                'sample_data' => 'Doe',
                'replacement' => '', // should be filled dynamically.
            ],
            'full_name' => [
                'label' => __('Recipient\'s full name'),
                'sample_data' => 'John Doe',
                'replacement' => '', // should be filled dynamically.
            ],
            'username' => [
                'label' => __('Recipient\'s username'),
                'sample_data' => 'johndoe',
                'replacement' => '', // should be filled dynamically.
            ],
            'email' => [
                'label' => __('Recipient\'s email address'),
                'sample_data' => config('mail.from.address', 'no-reply@example.com'),
                'replacement' => config('mail.from.address', 'no-reply@example.com'),
            ],
            'year' => [
                'label' => __('Current year'),
                'sample_data' => now()->year,
                'replacement' => now()->year,
            ],
            'date' => [
                'label' => __('Current date'),
                'sample_data' => now()->format('F j, Y'),
                'replacement' => now()->format('F j, Y'),
            ],
            'time' => [
                'label' => __('Current time'),
                'sample_data' => now()->format('F j, Y \a\t g:i A'),
                'replacement' => now()->format('F j, Y \a\t g:i A'),
            ],
            'current_year' => [
                'label' => __('Current year'),
                'sample_data' => now()->year,
                'replacement' => now()->year,
            ],
            'current_date' => [
                'label' => __('Current date'),
                'sample_data' => now()->format('F j, Y'),
                'replacement' => now()->format('F j, Y'),
            ],
            'current_time' => [
                'label' => __('Current time'),
                'sample_data' => now()->format('F j, Y \a\t g:i A'),
                'replacement' => now()->format('F j, Y \a\t g:i A'),
            ],
            'site_icon' => [
                'label' => __('Site Icon URL'),
                'sample_data' => asset(config('settings.site_icon') ?? '/images/logo/icon.png'),
                'replacement' => asset(config('settings.site_icon') ?? '/images/logo/icon.png'),
            ],
            'site_icon_image' => [
                'label' => __('Site Icon Image Tag'),
                'sample_data' => '<img src="' . asset(config('settings.site_icon') ?? '/images/logo/icon.png') . '" alt="Site Icon" style="max-width: 100px;margin: 0px auto;margin-bottom: 5px;">',
                'replacement' => '<img src="' . asset(config('settings.site_icon') ?? '/images/logo/icon.png') . '" alt="Site Icon" style="max-width: 100px;margin: 0px auto;margin-bottom: 5px;">',
            ],
            'app_name' => [
                'label' => __('Application Name'),
                'sample_data' => config('app.name', 'Your Company'),
                'replacement' => config('app.name', 'Your Company'),
            ],
            'app_url' => [
                'label' => __('Application URL'),
                'sample_data' => config('app.url', 'https://yourwebsite.com'),
                'replacement' => config('app.url', 'https://yourwebsite.com'),
            ],
            'company' => [
                'label' => __('Your company name'),
                'sample_data' => config('app.name', 'Your Company'),
                'replacement' => config('app.name', 'Your Company'),
            ],
            'company_name' => [
                'label' => __('Your company name'),
                'sample_data' => config('app.name', 'Your Company'),
                'replacement' => config('app.name', 'Your Company'),
            ],
            'company_website' => [
                'label' => __('Your company website URL'),
                'sample_data' => config('app.url', 'https://yourwebsite.com'),
                'replacement' => config('app.url', 'https://yourwebsite.com'),
            ],
        ]);
    }

    public function getAvailableVariables(): array
    {
        $variables = $this->getAllVariablesData();
        $availableVariables = [];
        foreach ($variables as $key => $data) {
            $availableVariables[] = [
                'label' => $data['label'],
                'value' => $key,
            ];
        }

        return $availableVariables;
    }

    public function getPreviewSampleData(): array
    {
        $variables = $this->getAllVariablesData();
        $sampleData = [];
        foreach ($variables as $key => $data) {
            $sampleData[$key] = $data['sample_data'];
        }
        return $sampleData;
    }

    public function getReplacementData(): array
    {
        $variables = $this->getAllVariablesData();
        $replacementData = [];
        foreach ($variables as $key => $data) {
            $replacementData[$key] = $data['replacement'];
        }
        return $replacementData;
    }

    public function appendUtmParametersToLinks(string $content, string $utmSource, string $utmMedium = 'email'): string
    {
        // This is a simplified example. In a real implementation, you would need to parse the HTML content
        // and append UTM parameters to each link properly.
        $utmParameters = http_build_query([
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
        ]);

        return preg_replace_callback(
            '/href=["\'](.*?)["\']/i',
            function ($matches) use ($utmParameters) {
                $url = $matches[1];
                $separator = strpos($url, '?') === false ? '?' : '&';
                return 'href="' . $url . $separator . $utmParameters . '"';
            },
            $content
        );
    }

    public function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $cleanValue = trim((string) $value);
            // Replace double or single curly brackets first to avoid conflicts.
            $content = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $cleanValue, $content);
        }

        // Remove any remaining unmatched variables (both single and double brackets).
        $content = preg_replace('/\{\{[^}]+\}\}/', '', $content);
        $content = preg_replace('/\{[^}]+\}/', '', $content);

        return $content;
    }
}
