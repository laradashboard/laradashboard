<?php

declare(strict_types=1);

namespace App\Livewire\Install;

use App\Models\User;
use App\Services\InstallationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('install.layout')]
class InstallWizard extends Component
{
    // Session key for storing wizard data
    protected const SESSION_KEY = 'install_wizard_data';

    // Properties to persist in session
    protected array $persistentProperties = [
        'dbDriver',
        'dbHost',
        'dbPort',
        'dbDatabase',
        'dbUsername',
        'dbPassword',
        'dbTestSuccess',
        'adminFirstName',
        'adminLastName',
        'adminEmail',
        'adminUsername',
        'siteName',
        'primaryColor',
        'adminUserId',
        'selectedModules',
    ];

    // Step tracking (synced with URL query parameter)
    #[Url(as: 'step')]
    public int $currentStep = 1;

    public int $totalSteps = 7;

    // Step 1: Requirements
    public array $requirements = [];

    // Step 2: Database
    public string $dbDriver = 'mysql';

    public string $dbHost = '127.0.0.1';

    public string $dbPort = '3306';

    public string $dbDatabase = '';

    public string $dbUsername = 'root';

    public string $dbPassword = '';

    public bool $dbTestSuccess = false;

    public string $dbTestMessage = '';

    // Step 3: APP_KEY
    public string $appKey = '';

    public bool $appKeyGenerated = false;

    // Step 4: Admin User
    public string $adminFirstName = '';

    public string $adminLastName = '';

    public string $adminEmail = '';

    public string $adminUsername = '';

    public string $adminPassword = '';

    public string $adminPasswordConfirmation = '';

    // Step 5: Site Settings
    public string $siteName = 'Lara Dashboard';

    public string $primaryColor = '#635bff';

    // Step 6: Modules Setup
    public array $availableModules = [];

    public array $selectedModules = [];

    public bool $modulesLoaded = false;

    public bool $modulesLoading = false;

    public string $modulesFetchError = '';

    public array $moduleInstallResults = [];

    // General state
    public bool $isProcessing = false;

    public string $errorMessage = '';

    public string $successMessage = '';

    // Store admin user ID for auto-login
    public ?int $adminUserId = null;

    protected InstallationService $installationService;

    public function boot(InstallationService $installationService): void
    {
        $this->installationService = $installationService;
    }

    public function mount(): void
    {
        // Restore wizard data from session
        $this->restoreFromSession();

        $this->requirements = app(InstallationService::class)->checkRequirements();

        // Check for existing APP_KEY
        if (app(InstallationService::class)->hasValidAppKey()) {
            $this->appKey = config('app.key');
            $this->appKeyGenerated = true;
        }

        // Validate step from URL (the #[Url] attribute handles binding)
        if ($this->currentStep < 1 || $this->currentStep > $this->totalSteps) {
            $this->currentStep = 1;
        }
    }

    /**
     * Save wizard data to session.
     */
    protected function saveToSession(): void
    {
        $data = [];
        foreach ($this->persistentProperties as $property) {
            $data[$property] = $this->{$property};
        }
        session([self::SESSION_KEY => $data]);
    }

    /**
     * Restore wizard data from session.
     */
    protected function restoreFromSession(): void
    {
        $data = session(self::SESSION_KEY, []);
        foreach ($this->persistentProperties as $property) {
            if (isset($data[$property])) {
                $this->{$property} = $data[$property];
            }
        }
    }

    /**
     * Clear wizard data from session.
     */
    protected function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function updatedDbDriver(): void
    {
        $this->dbPort = $this->installationService->getDefaultPort($this->dbDriver);
        $this->dbTestSuccess = false;
        $this->dbTestMessage = '';
    }

    public function testDatabaseConnection(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        $config = [
            'driver' => $this->dbDriver,
            'host' => $this->dbHost,
            'port' => $this->dbPort,
            'database' => $this->dbDriver === 'sqlite'
                ? database_path($this->dbDatabase ?: 'database.sqlite')
                : $this->dbDatabase,
            'username' => $this->dbUsername,
            'password' => $this->dbPassword,
        ];

        $result = $this->installationService->testDatabaseConnection($config);

        $this->dbTestSuccess = $result['success'];
        $this->dbTestMessage = $result['message'];

        if (! $result['success']) {
            $this->errorMessage = $result['message'];
        } else {
            // Save to session on successful connection test
            $this->saveToSession();
        }

        $this->isProcessing = false;
    }

    public function generateAppKey(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $this->installationService->generateAppKey();

            // Force page refresh because the new APP_KEY invalidates current session/CSRF token
            // Preserve current step using query parameter
            $this->redirect(route('install.welcome', ['step' => $this->currentStep]), navigate: true);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->isProcessing = false;
        }
    }

    public function nextStep(): void
    {
        // Guard against double-clicks while processing
        if ($this->isProcessing) {
            return;
        }

        $this->errorMessage = '';
        $this->successMessage = '';

        if (! $this->validateCurrentStep()) {
            return;
        }

        if (! $this->processCurrentStep()) {
            return;
        }

        // Save wizard data to session after successful step
        $this->saveToSession();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function skipStep(): void
    {
        // Guard against double-clicks while processing
        if ($this->isProcessing) {
            return;
        }

        // Only allow skipping optional steps
        if (! $this->isStepSkippable()) {
            return;
        }

        $this->errorMessage = '';
        $this->successMessage = '';

        // Save wizard data to session
        $this->saveToSession();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    #[Computed]
    public function isStepSkippable(): bool
    {
        return match ($this->currentStep) {
            3 => $this->appKeyGenerated, // APP Key - skip only if already generated
            5 => true, // Site Settings - has defaults
            6 => true, // Modules - optional
            default => false,
        };
    }

    public function previousStep(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep(): bool
    {
        return match ($this->currentStep) {
            1 => $this->validateRequirements(),
            2 => $this->validateDatabase(),
            3 => $this->validateAppKey(),
            4 => $this->validateAdminUser(),
            5 => $this->validateSiteSettings(),
            6 => true, // Modules step is optional
            default => true,
        };
    }

    protected function validateRequirements(): bool
    {
        if (! $this->installationService->allRequirementsPassed()) {
            $this->errorMessage = __('Please fix all requirements before continuing.');

            return false;
        }

        return true;
    }

    protected function validateDatabase(): bool
    {
        if (empty($this->dbDatabase)) {
            $this->errorMessage = __('Please enter a database name.');

            return false;
        }

        if (! $this->dbTestSuccess) {
            $this->errorMessage = __('Please test and verify your database connection first.');

            return false;
        }

        return true;
    }

    protected function validateAppKey(): bool
    {
        if (! $this->appKeyGenerated || empty($this->appKey)) {
            $this->errorMessage = __('Please generate an APP_KEY first.');

            return false;
        }

        return true;
    }

    protected function validateAdminUser(): bool
    {
        if (empty($this->adminFirstName)) {
            $this->errorMessage = __('Please enter a first name.');

            return false;
        }

        if (empty($this->adminLastName)) {
            $this->errorMessage = __('Please enter a last name.');

            return false;
        }

        if (empty($this->adminEmail)) {
            $this->errorMessage = __('Please enter an email address.');

            return false;
        }

        if (! filter_var($this->adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = __('Please enter a valid email address.');

            return false;
        }

        if (empty($this->adminUsername)) {
            $this->errorMessage = __('Please enter a username.');

            return false;
        }

        if (strlen($this->adminUsername) < 3) {
            $this->errorMessage = __('Username must be at least 3 characters.');

            return false;
        }

        if (empty($this->adminPassword)) {
            $this->errorMessage = __('Please enter a password.');

            return false;
        }

        if (strlen($this->adminPassword) < 8) {
            $this->errorMessage = __('Password must be at least 8 characters.');

            return false;
        }

        if ($this->adminPassword !== $this->adminPasswordConfirmation) {
            $this->errorMessage = __('Password confirmation does not match.');

            return false;
        }

        // Check if email already exists
        try {
            if (User::where('email', $this->adminEmail)->exists()) {
                $this->errorMessage = __('This email address is already registered. Please use a different email.');

                return false;
            }

            // Check if username already exists
            if (User::where('username', $this->adminUsername)->exists()) {
                $this->errorMessage = __('This username is already taken. Please choose a different username.');

                return false;
            }
        } catch (\Exception $e) {
            // Database might not be ready, skip uniqueness check
        }

        return true;
    }

    protected function validateSiteSettings(): bool
    {
        if (empty($this->siteName)) {
            $this->errorMessage = __('Please enter a site name.');

            return false;
        }

        return true;
    }

    protected function processCurrentStep(): bool
    {
        $this->isProcessing = true;

        try {
            return match ($this->currentStep) {
                2 => $this->processDatabaseStep(),
                4 => $this->processAdminUserStep(),
                5 => $this->processSiteSettingsStep(),
                6 => $this->processModulesStep(),
                default => true,
            };
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();

            return false;
        } finally {
            $this->isProcessing = false;
        }
    }

    protected function processDatabaseStep(): bool
    {
        $config = [
            'driver' => $this->dbDriver,
            'host' => $this->dbHost,
            'port' => $this->dbPort,
            'database' => $this->dbDriver === 'sqlite'
                ? database_path($this->dbDatabase ?: 'database.sqlite')
                : $this->dbDatabase,
            'username' => $this->dbUsername,
            'password' => $this->dbPassword,
        ];

        // Write database config to .env
        if (! $this->installationService->writeDatabaseConfig($config)) {
            $this->errorMessage = __('Failed to write database configuration to .env file.');

            return false;
        }

        // Small delay to ensure .env file is fully written
        usleep(100000); // 100ms

        // Reconnect to database
        $this->installationService->reconnectDatabase();

        // Run migrations
        $result = $this->installationService->runMigrations();

        if (! $result['success']) {
            $this->errorMessage = __('Failed to run migrations: ') . $result['message'];

            return false;
        }

        // Verify we can query the settings table.
        try {
            \Illuminate\Support\Facades\DB::table('settings')->count();
        } catch (\Exception $e) {
            $this->errorMessage = __('Migrations completed but cannot query settings table: ') . $e->getMessage();

            return false;
        }

        return true;
    }

    protected function processAdminUserStep(): bool
    {
        try {
            $user = $this->installationService->createAdminUser([
                'first_name' => $this->adminFirstName,
                'last_name' => $this->adminLastName,
                'email' => $this->adminEmail,
                'username' => $this->adminUsername,
                'password' => $this->adminPassword,
            ]);

            // Store user ID for auto-login after installation completes
            $this->adminUserId = $user->id;

            return true;
        } catch (\Exception $e) {
            $this->errorMessage = __('Failed to create admin user: ') . $e->getMessage();

            return false;
        }
    }

    protected function processSiteSettingsStep(): bool
    {
        try {
            // Reconnect database to ensure we have valid connection
            $this->installationService->reconnectDatabase();

            // Verify settings table exists before attempting to save
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $this->errorMessage = __('Settings table does not exist. Please go back to the Database step and try again.');

                return false;
            }

            $this->installationService->saveSiteSettings([
                'app_name' => $this->siteName,
                'primary_color' => $this->primaryColor,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->errorMessage = __('Failed to save site settings: ') . $e->getMessage();

            return false;
        }
    }

    /**
     * Load available modules from marketplace or local fallback.
     */
    public function loadModulesFromMarketplace(): void
    {
        if ($this->modulesLoaded || $this->modulesLoading) {
            return;
        }

        $this->modulesLoading = true;
        $this->modulesFetchError = '';

        try {
            // Read slugs from modules_statuses.json
            $statuses = $this->installationService->getModuleSlugsFromStatuses();
            $slugs = array_map('strtolower', array_keys($statuses));

            if (empty($slugs)) {
                $this->availableModules = [];
                $this->modulesLoaded = true;
                $this->modulesLoading = false;

                return;
            }

            // Try marketplace API first
            $result = $this->installationService->fetchMarketplaceModules($slugs);

            if ($result['success'] && ! empty($result['modules'])) {
                $this->availableModules = $result['modules'];
            } else {
                // Fallback to local modules
                $this->availableModules = $this->installationService->getLocalModules($slugs);

                if (! empty($result['error'])) {
                    $this->modulesFetchError = $result['error'];
                }
            }

            // Filter out pro/freemium modules — they require license activation
            $this->availableModules = array_values(array_filter(
                $this->availableModules,
                fn (array $module) => ($module['is_free'] ?? true) && ($module['module_type'] ?? 'free') === 'free'
            ));

            // Pre-select all modules that were enabled in modules_statuses.json
            $this->selectedModules = [];
            $normalizedStatuses = array_change_key_case($statuses, CASE_LOWER);
            foreach ($this->availableModules as $module) {
                $slug = $module['slug'];

                if (($normalizedStatuses[$slug] ?? false) === true) {
                    $this->selectedModules[] = $slug;
                }
            }
        } catch (\Exception $e) {
            // Ensure loading completes even if something goes wrong
            $this->modulesFetchError = __('Failed to load modules: ') . $e->getMessage();
            $this->availableModules = [];
        }

        $this->modulesLoaded = true;
        $this->modulesLoading = false;
    }

    /**
     * Toggle a module selection.
     */
    public function toggleModule(string $slug): void
    {
        // Don't allow toggling paid modules (require license activation)
        $module = collect($this->availableModules)->firstWhere('slug', $slug);
        if ($module && ! ($module['is_free'] ?? true)) {
            return;
        }

        if (in_array($slug, $this->selectedModules)) {
            $this->selectedModules = array_values(array_diff($this->selectedModules, [$slug]));
        } else {
            $this->selectedModules[] = $slug;
        }
    }

    /**
     * Select all available modules.
     */
    public function selectAllModules(): void
    {
        $this->selectedModules = array_map(
            fn (array $module) => $module['slug'],
            array_filter($this->availableModules, fn (array $module) => $module['is_free'] ?? true)
        );
    }

    /**
     * Deselect all modules.
     */
    public function deselectAllModules(): void
    {
        $this->selectedModules = [];
    }

    /**
     * Process the modules step - download, migrate and enable selected modules.
     */
    protected function processModulesStep(): bool
    {
        $this->moduleInstallResults = [];

        if (empty($this->selectedModules)) {
            return true;
        }

        $modulesPath = config('modules.paths.modules', base_path('modules'));
        $marketplaceUrl = rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');

        /** @var \App\Services\Modules\ModuleService $moduleService */
        $moduleService = app(\App\Services\Modules\ModuleService::class);

        foreach ($this->selectedModules as $slug) {
            $module = collect($this->availableModules)->firstWhere('slug', $slug);

            if (! $module) {
                continue;
            }

            try {
                // Check if module already exists locally
                $isLocal = ($module['is_local'] ?? false) || $this->moduleExistsLocally($slug, $modulesPath);

                if (! $isLocal) {
                    // Build download URL from marketplace
                    $version = $module['version'] ?? '1.0.0';
                    $downloadUrl = $module['download_url'] ?? "{$marketplaceUrl}/api/modules/{$slug}/download/{$version}";

                    // Download and install from marketplace
                    $downloadResult = $this->installationService->downloadAndInstallModule($slug, $downloadUrl);

                    if (! $downloadResult['success']) {
                        $this->moduleInstallResults[$slug] = [
                            'success' => false,
                            'message' => $downloadResult['message'],
                        ];

                        continue;
                    }
                }

                // Get the original module name from module.json for migrations and status
                $originalName = $slug;
                $folderName = $moduleService->getActualModuleFolderName($slug);
                if ($folderName) {
                    $jsonName = $moduleService->getModuleJsonName($slug);
                    if ($jsonName) {
                        $originalName = $jsonName;
                    }
                }

                // Run migrations
                $moduleService->runModuleMigrations($originalName);

                // Enable module by writing directly to modules_statuses.json
                // artisan module:enable won't work for freshly downloaded modules
                // because nwidart caches the module list at boot time
                $moduleService->setModuleStatus($originalName, true);

                $this->moduleInstallResults[$slug] = [
                    'success' => true,
                    'message' => __('Installed and enabled'),
                ];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Install wizard: failed to process module {$slug}: " . $e->getMessage());

                $this->moduleInstallResults[$slug] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return true;
    }

    /**
     * Check if a module already exists in the local modules directory.
     */
    protected function moduleExistsLocally(string $slug, string $modulesPath): bool
    {
        if (! is_dir($modulesPath)) {
            return false;
        }

        foreach (scandir($modulesPath) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $moduleJsonPath = $modulesPath . '/' . $folder . '/module.json';

            if (! file_exists($moduleJsonPath)) {
                continue;
            }

            $data = json_decode(file_get_contents($moduleJsonPath), true);
            $name = strtolower($data['name'] ?? $folder);

            if ($name === strtolower($slug)) {
                return true;
            }
        }

        return false;
    }

    public function completeInstallation(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $this->installationService->completeInstallation();

            // Auto-login the admin user
            if ($this->adminUserId) {
                $user = User::find($this->adminUserId);
                if ($user) {
                    Auth::login($user);
                }
            }

            // Redirect to admin dashboard
            $this->redirect(route('admin.dashboard'), navigate: true);
        } catch (\Exception $e) {
            $this->errorMessage = __('Failed to complete installation: ') . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function getStepTitle(): string
    {
        return match ($this->currentStep) {
            1 => __('Requirements Check'),
            2 => __('Database Configuration'),
            3 => __('Application Key'),
            4 => __('Admin Account'),
            5 => __('Site Settings'),
            6 => __('Modules Setup'),
            7 => __('Installation Complete'),
            default => '',
        };
    }

    public function getStepDescription(): string
    {
        return match ($this->currentStep) {
            1 => __('Check if your server meets all requirements'),
            2 => __('Configure your database connection'),
            3 => __('Generate or verify your application encryption key'),
            4 => __('Create your administrator account'),
            5 => __('Configure basic site settings'),
            6 => __('Select modules to install from the marketplace'),
            7 => __('Your installation is complete!'),
            default => '',
        };
    }

    public function getDrivers(): array
    {
        return $this->installationService->getAvailableDrivers();
    }

    public function render()
    {
        return view('livewire.install.install-wizard');
    }
}
