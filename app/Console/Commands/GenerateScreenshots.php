<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;

class GenerateScreenshots extends Command
{
    protected $signature = 'app:generate-screenshots {--debug}';
    protected $description = 'Generate screenshots of application pages';

    public function handle()
    {
        $defaultWidth = 1280;
        $defaultHeight = 800;

        // Generate guest pages
        $this->info('Generating guest pages screenshots...');
        foreach ($this->guestModePages() as $route) {
            $this->generateScreenshot($route, $defaultWidth, $defaultHeight);
        }

        // Generate authenticated pages
        $this->info('Generating authenticated pages screenshots...');
        foreach ($this->authenticatedPages() as $route) {
            $this->generateAuthenticatedScreenshot($route, $defaultWidth, $defaultHeight);
        }

        $this->info('Screenshots updated!');
    }

    private function generateScreenshot($route, $defaultWidth, $defaultHeight)
    {
        $browsershot = Browsershot::url(config('app.url') . $route['url'])
            ->waitUntilNetworkIdle()
            ->windowSize($route['width'] ?? $defaultWidth, $route['height'] ?? $defaultHeight);

        if (isset($route['mode']) && $route['mode'] === 'dark') {
            // Auth pages use .dark-mode-toggle class (not #darkModeToggle)
            $browsershot->click('.dark-mode-toggle')->waitUntilNetworkIdle();
        }

        $browsershot->save("demo-screenshots/{$route['name']}.png");
        $this->info("Generated: {$route['name']}.png");
    }

    private function generateAuthenticatedScreenshot($route, $defaultWidth, $defaultHeight)
    {
        try {
            // Use the bypass route with target parameter
            $loginUrl = config(key: 'app.url') . '/screenshot-login/superadmin@example.com?target=' . urlencode($route['url']);

            $browser = Browsershot::url($loginUrl)
                ->setDelay(500)
                ->setOption('args', [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-web-security',
                    '--disable-features=VizDisplayCompositor',
                ])
                ->waitUntilNetworkIdle();

            // Apply dark mode if needed.
            if (isset($route['mode']) && $route['mode'] === 'dark') {
                $browser->click('#darkModeToggle')->setDelay(500)->waitUntilNetworkIdle(true);
            }

            // Perform custom actions if specified.
            if (isset($route['customAction'])) {
                $browser->click($route['customAction'])->setDelay(500);
            }

            // If a specific area is selected, scroll to it.
            if (isset($route['selected-area'])) {
                $browser->select($route['selected-area'])->setDelay(500);
            }

            // Take final screenshot.
            $browser->windowSize($route['width'] ?? $defaultWidth, $route['height'] ?? $defaultHeight)
                ->save("demo-screenshots/{$route['name']}.png");

            $this->info("Generated: {$route['name']}.png");
        } catch (\Exception $e) {
            $this->error("Failed to generate {$route['name']}: " . $e->getMessage());
        }
    }

    public function guestModePages()
    {
        return [
            [
                'name' => '00-Login-Page-Lite-Mode',
                'url' => '/admin/login',
                'mode' => 'lite',
            ],
            [
                'name' => '01-Forget-password',
                'url' => '/admin/password/reset',
                'mode' => 'dark',
            ],
            [
                'name' => '100-Custom-Error-Pages',
                'url' => '/404',
                'mode' => 'lite',
            ],
            [
                'name' => '120-Rest-API-Documentation',
                'url' => '/docs/api#/',
                'mode' => 'lite',
            ],
            [
                'name' => '121-Rest-API-Login',
                'url' => '/docs/api#/operations/auth.login',
                'mode' => 'lite',
            ],
        ];
    }

    public function authenticatedPages()
    {
        return [
            // Dashboard pages
            [
                'name' => '03-Dashboard-Page-lite-Mode',
                'url' => '/admin',
                'mode' => 'lite',
            ],
            [
                'name' => '04-Dashboard-Page-Dark-Mode',
                'url' => '/admin',
                'mode' => 'dark',
            ],
            [
                'name' => '04_1-Dashboard-Collapsed-Sidebar',
                'url' => '/admin',
                'mode' => 'lite',
                'customAction' => '#sidebar-toggle-button',
            ],
            // Roles pages
            [
                'name' => '05-Role-List-Lite',
                'url' => '/admin/roles',
                'mode' => 'lite',
            ],
            [
                'name' => '06-Role-List-Dark',
                'url' => '/admin/roles',
                'mode' => 'dark',
            ],
            [
                'name' => '07-Role-Create',
                'url' => '/admin/roles/create',
                'mode' => 'lite',
            ],
            [
                'name' => '08-Role-Edit',
                'url' => '/admin/roles/1/edit',
                'mode' => 'lite',
            ],

            // Permissions page
            [
                'name' => '09-Permissions-List-Lite-Mode',
                'url' => '/admin/permissions',
                'mode' => 'lite',
            ],

            // Users pages
            [
                'name' => '10-User-List-Lite-Mode',
                'url' => '/admin/users',
                'mode' => 'lite',
            ],
            [
                'name' => '11-User-Create-Dark-Mode',
                'url' => '/admin/users/create',
                'mode' => 'dark',
            ],
            [
                'name' => '12-Profile-Edit-Lite-Mode',
                'url' => '/profile/edit',
                'mode' => 'lite',
            ],

            // Modules pages
            [
                'name' => '14-Module-List',
                'url' => '/admin/modules',
                'mode' => 'lite',
            ],
            [
                'name' => '15-Module-Install',
                'url' => '/admin/modules/upload',
                'mode' => 'lite',
            ],

            // Action log
            [
                'name' => '20-Action-Log-List',
                'url' => '/admin/action-log',
                'mode' => 'lite',
            ],

            // Posts/content pages
            [
                'name' => '30-Post-List-Dark-Mode',
                'url' => '/admin/posts',
                'mode' => 'dark',
            ],
            [
                'name' => '31-Post-List-Lite-Mode',
                'url' => '/admin/posts',
                'mode' => 'lite',
            ],
            [
                'name' => '32-Post-Create-Lite-Mode',
                'url' => '/admin/posts/post/create',
                'mode' => 'lite',
            ],
            [
                'name' => '33-Post-Edit-Dark-Mode',
                'url' => '/admin/posts/post/1/edit',
                'mode' => 'lite',
            ],
            [
                'name' => '34-Category-List-Lite-Mode',
                'url' => '/admin/terms/category',
                'mode' => 'lite',
            ],
            [
                'name' => '35-Category-Edit-Dark-Mode',
                'url' => '/admin/terms/category/1/edit',
                'mode' => 'dark',
            ],
            [
                'name' => '36-Tags-List-Lite-Mode',
                'url' => '/admin/terms/tag',
                'mode' => 'lite',
            ],
            [
                'name' => '37-Tags-Edit-Dark-Mode',
                'url' => '/admin/terms/tag/2/edit',
                'mode' => 'dark',
            ],
            [
                'name' => '38-Pages-List-Lite-Mode',
                'url' => '/admin/posts/page',
                'mode' => 'lite',
            ],
            [
                'name' => '39-Pages-Edit-Dark-Mode',
                'url' => '/admin/posts/page/2/edit',
                'mode' => 'lite',
            ],

            // Settings pages
            [
                'name' => '40-Settings-General',
                'url' => '/admin/settings?tab=general',
                'mode' => 'lite',
            ],
            [
                'name' => '41-Settings-Site-Appearance-Dark-Mode',
                'url' => '/admin/settings?tab=appearance',
                'mode' => 'dark',
            ],
            [
                'name' => '42-Settings-Content',
                'url' => '/admin/settings?tab=content',
                'mode' => 'lite',
            ],
            [
                'name' => '43-Settings-Integration',
                'url' => '/admin/settings?tab=integrations',
                'mode' => 'lite',
            ],
            [
                'name' => '44-Settings-Performance-Security',
                'url' => '/admin/settings?tab=performance-security',
                'mode' => 'lite',
            ],

            // Translation pages
            [
                'name' => '50-Translation-List-Lite-Mode',
                'url' => '/admin/translations',
                'mode' => 'lite',
            ],
            [
                'name' => '51-Translation-List-Dark-Mode',
                'url' => '/admin/translations',
                'mode' => 'dark',
            ],

            // Media pages
            [
                'name' => '60-Media-List-Lite-Mode',
                'url' => '/admin/media',
                'mode' => 'lite',
            ],
            [
                'name' => '61-Media-List-Dark-Mode',
                'url' => '/admin/media',
                'mode' => 'dark',
            ],

            // AI pages
            [
                'name' => '70-AI-Providers-Lite-Mode',
                'url' => '/admin/ai/providers',
                'mode' => 'lite',
            ],

            // Email management pages
            [
                'name' => '72-Email-Connections-Lite-Mode',
                'url' => '/admin/settings/email-connections',
                'mode' => 'lite',
            ],
            [
                'name' => '73-Email-Templates-Lite-Mode',
                'url' => '/admin/settings/email-templates',
                'mode' => 'lite',
            ],
            [
                'name' => '74-Email-Templates-Create-Dark-Mode',
                'url' => '/admin/settings/email-templates/create',
                'mode' => 'lite',
            ],

            // Notifications pages
            [
                'name' => '75-Notifications-Lite-Mode',
                'url' => '/admin/settings/notifications',
                'mode' => 'lite',
            ],

            // Detail/Show pages
            [
                'name' => '16-Module-Detail-Lite-Mode',
                'url' => '/admin/modules/Crm',
                'mode' => 'lite',
            ],
            [
                'name' => '09_1-Permission-Detail-Lite-Mode',
                'url' => '/admin/permissions/1',
                'mode' => 'lite',
            ],
            [
                'name' => '08_1-Role-Detail-Lite-Mode',
                'url' => '/admin/roles/1',
                'mode' => 'lite',
            ],
            [
                'name' => '12_1-User-Detail-Lite-Mode',
                'url' => '/admin/users/1',
                'mode' => 'lite',
            ],

            // Other features
            [
                'name' => '91-Laravel-Pulse-Dashboard-for-Monitoring',
                'url' => '/pulse',
                'mode' => 'lite',
            ],
            [
                'name' => '101-Beautiful-Drawer-component-Category-Add',
                'url' => '/admin/posts/post/create',
                'mode' => 'lite',
                'customAction' => '#term-drawer-category button',
            ],
        ];
    }
}
