<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

class InstallationService
{
    /**
     * Check if database is configured (without connecting).
     * This is a quick check that can be used by service providers
     * to avoid database queries when installation is not complete.
     */
    public static function isDatabaseConfigured(): bool
    {
        // Skip check in testing environment
        if (app()->environment('testing')) {
            return true;
        }

        // Skip check in console (artisan commands need to work)
        if (app()->runningInConsole()) {
            return true;
        }

        $driver = env('DB_CONNECTION', config('database.default'));

        // For SQLite, just check if driver is set
        if ($driver === 'sqlite') {
            return true;
        }

        // For other drivers, check if database name is configured
        $database = env('DB_DATABASE');

        return ! empty($database);
    }

    /**
     * Check if installation appears to be complete (without database query).
     * Returns true if basic configuration seems complete.
     * For full verification, use the middleware check.
     */
    public static function isLikelyInstalled(): bool
    {
        // If database isn't configured, definitely not installed
        if (! self::isDatabaseConfigured()) {
            return false;
        }

        // Check if APP_KEY is valid
        $key = config('app.key');
        if (empty($key)) {
            return false;
        }

        // Try to connect and check installation_completed setting
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return false;
            }

            $setting = \Illuminate\Support\Facades\DB::table('settings')
                ->where('option_name', Setting::INSTALLATION_COMPLETED)
                ->first();

            return $setting && $setting->option_value === '1';
        } catch (\Exception $e) {
            return false;
        }
    }

    protected array $requiredExtensions = [
        'pdo',
        'mbstring',
        'openssl',
        'tokenizer',
        'xml',
        'ctype',
        'json',
        'bcmath',
        'fileinfo',
        'curl',
    ];

    protected array $writableDirectories = [
        'storage/app',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    public function __construct(
        protected EnvWriter $envWriter,
        protected SettingService $settingService,
        protected PermissionService $permissionService,
        protected RolesService $rolesService
    ) {
    }

    /**
     * Check all system requirements.
     */
    public function checkRequirements(): array
    {
        return [
            'php' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'directories' => $this->checkDirectories(),
            'env_writable' => $this->checkEnvWritable(),
        ];
    }

    /**
     * Check PHP version requirement.
     */
    public function checkPhpVersion(): array
    {
        $required = '8.2.0';
        $current = PHP_VERSION;
        $passed = version_compare($current, $required, '>=');

        return [
            'required' => $required,
            'current' => $current,
            'passed' => $passed,
        ];
    }

    /**
     * Check required PHP extensions.
     */
    public function checkExtensions(): array
    {
        $results = [];

        foreach ($this->requiredExtensions as $extension) {
            $results[$extension] = extension_loaded($extension);
        }

        return $results;
    }

    /**
     * Check if required directories are writable.
     */
    public function checkDirectories(): array
    {
        $results = [];

        foreach ($this->writableDirectories as $directory) {
            $path = base_path($directory);
            $results[$directory] = is_dir($path) && is_writable($path);
        }

        return $results;
    }

    /**
     * Check if .env file is writable.
     */
    public function checkEnvWritable(): bool
    {
        $envPath = base_path('.env');

        return file_exists($envPath) && is_writable($envPath);
    }

    /**
     * Check if all requirements pass.
     */
    public function allRequirementsPassed(): bool
    {
        $requirements = $this->checkRequirements();

        // Check PHP version
        if (! $requirements['php']['passed']) {
            return false;
        }

        // Check extensions
        foreach ($requirements['extensions'] as $passed) {
            if (! $passed) {
                return false;
            }
        }

        // Check directories
        foreach ($requirements['directories'] as $passed) {
            if (! $passed) {
                return false;
            }
        }

        // Check .env writable
        if (! $requirements['env_writable']) {
            return false;
        }

        return true;
    }

    /**
     * Test database connection with given configuration.
     */
    public function testDatabaseConnection(array $config): array
    {
        try {
            $dsn = $this->buildDsn($config);

            if ($config['driver'] === 'sqlite') {
                // For SQLite, check if database file exists or can be created
                $dbPath = $config['database'];
                if ($dbPath !== ':memory:') {
                    $directory = dirname($dbPath);
                    if (! is_dir($directory)) {
                        return [
                            'success' => false,
                            'message' => __('Directory does not exist: :path', ['path' => $directory]),
                        ];
                    }
                    if (! is_writable($directory)) {
                        return [
                            'success' => false,
                            'message' => __('Directory is not writable: :path', ['path' => $directory]),
                        ];
                    }
                }
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ];

            if ($config['driver'] === 'sqlite') {
                new PDO($dsn, null, null, $options);
            } else {
                new PDO($dsn, $config['username'], $config['password'], $options);
            }

            return [
                'success' => true,
                'message' => __('Database connection successful!'),
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $this->formatDatabaseError($e->getMessage()),
            ];
        }
    }

    /**
     * Build DSN string for PDO connection.
     */
    protected function buildDsn(array $config): string
    {
        return match ($config['driver']) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                $config['host'],
                $config['port'],
                $config['database']
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $config['host'],
                $config['port'],
                $config['database']
            ),
            'sqlite' => sprintf('sqlite:%s', $config['database']),
            'sqlsrv' => sprintf(
                'sqlsrv:Server=%s,%s;Database=%s',
                $config['host'],
                $config['port'],
                $config['database']
            ),
            default => throw new \InvalidArgumentException(__('Unsupported database driver: :driver', ['driver' => $config['driver']])),
        };
    }

    /**
     * Format database error message to be more user-friendly.
     */
    protected function formatDatabaseError(string $message): string
    {
        // Connection refused
        if (str_contains($message, 'Connection refused')) {
            return __('Could not connect to database server. Please check if the server is running and the host/port are correct.');
        }

        // Unknown database
        if (str_contains($message, 'Unknown database')) {
            return __('Database does not exist. Please create the database first.');
        }

        // Access denied
        if (str_contains($message, 'Access denied')) {
            return __('Access denied. Please check your username and password.');
        }

        // Host not found
        if (str_contains($message, 'getaddrinfo failed') || str_contains($message, 'No such host')) {
            return __('Database host not found. Please check the hostname.');
        }

        return $message;
    }

    /**
     * Write database configuration to .env file.
     */
    public function writeDatabaseConfig(array $config): bool
    {
        try {
            $this->envWriter->write('DB_CONNECTION', $config['driver']);
            $this->envWriter->write('DB_HOST', $config['host'] ?? '127.0.0.1');
            $this->envWriter->write('DB_PORT', (string) ($config['port'] ?? '3306'));
            $this->envWriter->write('DB_DATABASE', $config['database']);
            $this->envWriter->write('DB_USERNAME', $config['username'] ?? '');
            $this->envWriter->write('DB_PASSWORD', $config['password'] ?? '');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate and write APP_KEY to .env file.
     */
    public function generateAppKey(): string
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $this->envWriter->write('APP_KEY', $key);

        // Update the runtime config
        config(['app.key' => $key]);

        return $key;
    }

    /**
     * Check if APP_KEY exists and is valid.
     */
    public function hasValidAppKey(): bool
    {
        $key = config('app.key');

        if (empty($key)) {
            // Try reading from .env directly
            $key = $this->envWriter->get('APP_KEY');
        }

        if (empty($key)) {
            return false;
        }

        // Remove quotes if present
        $key = trim($key, '"\'');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return $decoded !== false && strlen($decoded) === 32;
        }

        return strlen($key) === 32;
    }

    /**
     * Run database migrations.
     */
    public function runMigrations(): array
    {
        try {
            // Reconnect to database with new configuration
            $this->reconnectDatabase();

            // Run migrations with explicit database and force flag
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            if ($exitCode !== 0) {
                return [
                    'success' => false,
                    'message' => __('Migration failed with exit code: ') . $exitCode . "\n" . $output,
                ];
            }

            // Verify that tables were actually created
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return [
                    'success' => false,
                    'message' => __('Migration completed but settings table was not created. Output: ') . $output,
                ];
            }

            return [
                'success' => true,
                'message' => __('Migrations completed successfully!'),
                'output' => $output,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reconnect to database after config change.
     */
    public function reconnectDatabase(): void
    {
        // Clear PHP file stat cache
        clearstatcache(true);

        // Clear the cached configuration
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Remove cached config files if they exist
        $cachedConfigPath = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfigPath)) {
            @unlink($cachedConfigPath);
        }

        // Re-read .env file
        $dotenv = \Dotenv\Dotenv::createMutable(base_path());
        $dotenv->load();

        // Get current values from .env
        $driver = env('DB_CONNECTION', 'mysql');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', 'forge');
        $username = env('DB_USERNAME', 'forge');
        $password = env('DB_PASSWORD', '');

        // Update config with new values
        config([
            'database.default' => $driver,
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
            'database.connections.pgsql.host' => $host,
            'database.connections.pgsql.port' => env('DB_PORT', '5432'),
            'database.connections.pgsql.database' => $database,
            'database.connections.pgsql.username' => $username,
            'database.connections.pgsql.password' => $password,
            'database.connections.sqlite.database' => $driver === 'sqlite' ? $database : database_path('database.sqlite'),
            'database.connections.sqlsrv.host' => $host,
            'database.connections.sqlsrv.port' => env('DB_PORT', '1433'),
            'database.connections.sqlsrv.database' => $database,
            'database.connections.sqlsrv.username' => $username,
            'database.connections.sqlsrv.password' => $password,
        ]);

        // Purge all connections to ensure clean slate
        DB::purge('mysql');
        DB::purge('pgsql');
        DB::purge('sqlite');
        DB::purge('sqlsrv');

        // Set the default connection
        DB::setDefaultConnection($driver);

        // Reconnect using the new configuration
        DB::reconnect($driver);
    }

    /**
     * Ensure permissions and roles exist in the database.
     */
    public function ensureRolesAndPermissions(): void
    {
        // Create all permissions
        $this->permissionService->createPermissions();

        // Create predefined roles with their permissions (uses RolesService for consistency)
        $this->rolesService->createPredefinedRoles();
    }

    /**
     * Create the admin user.
     */
    public function createAdminUser(array $data): User
    {
        // Ensure roles and permissions exist
        $this->ensureRolesAndPermissions();

        // Create the user
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        // Assign Superadmin role
        $user->assignRole('Superadmin');

        return $user;
    }

    /**
     * Save site settings.
     */
    public function saveSiteSettings(array $settings): void
    {
        // Save app name to database settings (used by settings page and views)
        if (isset($settings['app_name'])) {
            $this->settingService->addSetting(Setting::APP_NAME, $settings['app_name'], true);

            // Also update APP_NAME in .env
            $this->envWriter->write('APP_NAME', $settings['app_name']);
        }

        // Save primary color (theme_primary_color is used throughout the app)
        if (isset($settings['primary_color'])) {
            $this->settingService->addSetting(Setting::THEME_PRIMARY_COLOR, $settings['primary_color'], true);
        }

        // Save any other settings
        foreach ($settings as $key => $value) {
            if (! in_array($key, ['app_name', 'primary_color'])) {
                $this->settingService->addSetting($key, $value, true);
            }
        }
    }

    /**
     * Complete the installation process.
     */
    public function completeInstallation(): void
    {
        // Set the installation completed flag
        $this->settingService->addSetting(Setting::INSTALLATION_COMPLETED, '1', true);

        // Create the .installed flag file (used by Telescope and other services to know installation is complete)
        $this->createInstalledFlagFile();

        // Clear all caches
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            // Cache clearing might fail, but that's okay
        }

        // Setup storage link
        try {
            if (! file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        } catch (\Exception $e) {
            // Storage link might already exist
        }
    }

    /**
     * Create the .installed flag file.
     * This file is used by Telescope and other services to quickly check if installation is complete.
     */
    protected function createInstalledFlagFile(): void
    {
        $flagFile = storage_path('app/.installed');
        if (! file_exists($flagFile)) {
            file_put_contents($flagFile, date('Y-m-d H:i:s'));
        }
    }

    /**
     * Check if the .installed flag file exists.
     */
    public static function isInstalledFlagExists(): bool
    {
        return file_exists(storage_path('app/.installed'));
    }

    /**
     * Get default port for database driver.
     */
    public function getDefaultPort(string $driver): string
    {
        return match ($driver) {
            'mysql' => '3306',
            'pgsql' => '5432',
            'sqlsrv' => '1433',
            default => '',
        };
    }

    /**
     * Get available database drivers.
     */
    public function getAvailableDrivers(): array
    {
        return [
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
            'sqlite' => 'SQLite',
            'sqlsrv' => 'SQL Server',
        ];
    }
}
