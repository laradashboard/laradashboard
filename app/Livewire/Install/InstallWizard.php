<?php

declare(strict_types=1);

namespace App\Livewire\Install;

use App\Models\User;
use App\Services\InstallationService;
use Illuminate\Support\Facades\Auth;
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
    ];

    // Step tracking (synced with URL query parameter)
    #[Url(as: 'step')]
    public int $currentStep = 1;

    public int $totalSteps = 6;

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
            6 => __('Installation Complete'),
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
            6 => __('Your installation is complete!'),
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
