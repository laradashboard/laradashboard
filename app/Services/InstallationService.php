<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Services\Modules\ModuleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        // Increase execution time for migrations (they can take a while)
        $originalTimeout = (int) ini_get('max_execution_time');
        set_time_limit(300); // 5 minutes for migrations

        try {
            // Reconnect to database with new configuration
            $this->reconnectDatabase();

            // Check if core tables already exist (database was previously fully migrated)
            $coreTablesExist = \Illuminate\Support\Facades\Schema::hasTable('settings')
                && \Illuminate\Support\Facades\Schema::hasTable('users');

            if ($coreTablesExist) {
                // Core tables exist - run only pending migrations
                return $this->runMigrateCommand();
            }

            // Fresh or partial install - run all migrations
            $result = $this->runMigrateCommand();

            if ($result['success']) {
                // Verify core table was created
                if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return [
                        'success' => false,
                        'message' => __('Migration completed but settings table was not created. Output: ') . ($result['output'] ?? ''),
                    ];
                }

                return $result;
            }

            // Migration failed - check if it's a "table already exists" error
            if (! $this->isTableExistsError($result['message'] ?? '')) {
                return $result;
            }

            // Tables from a previous install or another app exist in this database.
            // Drop conflicting tables that aren't tracked in the migrations table, then retry.
            Log::warning('Migration failed due to existing tables, attempting to resolve: ' . ($result['message'] ?? ''));

            $this->dropConflictingTables();

            // Retry migrations after dropping conflicting tables
            $retryResult = $this->runMigrateCommand();

            if ($retryResult['success']) {
                if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return [
                        'success' => false,
                        'message' => __('Migration completed but settings table was not created. Output: ') . ($retryResult['output'] ?? ''),
                    ];
                }
            }

            return $retryResult;
        } catch (\Exception $e) {
            // Handle "table already exists" exception gracefully
            if ($this->isTableExistsError($e->getMessage())) {
                // Check if core tables ended up created despite the error
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')
                    && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                    Log::warning('Migration exception for existing tables (handled): ' . $e->getMessage());

                    return [
                        'success' => true,
                        'message' => __('Database tables already exist. Skipped existing migrations.'),
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            // Restore original timeout
            set_time_limit($originalTimeout);
        }
    }

    /**
     * Run the migrate artisan command and return a normalized result.
     */
    protected function runMigrateCommand(): array
    {
        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            if ($exitCode !== 0) {
                // If it's a "table already exists" error but core tables are present, that's OK
                if ($this->isTableExistsError($output)
                    && \Illuminate\Support\Facades\Schema::hasTable('settings')
                    && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                    Log::warning('Some migrations skipped due to existing tables: ' . $output);

                    return [
                        'success' => true,
                        'message' => __('Database tables already exist. Skipped existing migrations.'),
                        'output' => $output,
                    ];
                }

                return [
                    'success' => false,
                    'message' => __('Migration failed with exit code: ') . $exitCode . "\n" . $output,
                    'output' => $output,
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
                'output' => '',
            ];
        }
    }

    /**
     * Check if an error message indicates a "table already exists" problem.
     */
    protected function isTableExistsError(string $message): bool
    {
        return str_contains($message, 'already exists')
            || str_contains($message, '42S01');
    }

    /**
     * Drop tables that exist in the database but are not tracked in the migrations table.
     * This handles cases where a previous install or another app left orphan tables.
     */
    protected function dropConflictingTables(): void
    {
        $connection = DB::connection();
        $schema = $connection->getSchemaBuilder();

        // Get all tables currently in the database
        $existingTables = $schema->getTableListing();

        // The migrations table itself must be kept
        $protectedTables = ['migrations'];

        // Get tables that are tracked by already-run migrations
        $trackedTables = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
            // We can't easily map migration files to table names, so instead
            // we'll just drop all non-migration-tracked tables and let migrate recreate them.
            // But safer approach: drop only known problematic vendor tables.
        }

        // Known vendor tables that commonly conflict (from packages like Telescope, Horizon, etc.)
        $knownVendorTables = [
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($knownVendorTables as $table) {
                if (in_array($table, $existingTables) && ! in_array($table, $protectedTables)) {
                    $schema->dropIfExists($table);
                    Log::info("Dropped conflicting table: {$table}");
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
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

    /**
     * Read module slugs from modules_statuses.json.
     *
     * @return array<string, bool>
     */
    public function getModuleSlugsFromStatuses(): array
    {
        $path = base_path('modules_statuses.json');

        if (! file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        $statuses = json_decode($contents, true);

        if (! is_array($statuses)) {
            return [];
        }

        return $statuses;
    }

    /**
     * Fetch module details from the marketplace API by slugs.
     *
     * @param  array<string>  $slugs
     * @return array{success: bool, modules: array, error: string|null}
     */
    public function fetchMarketplaceModules(array $slugs): array
    {
        if (empty($slugs)) {
            return ['success' => true, 'modules' => [], 'error' => null];
        }

        $marketplaceUrl = rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');

        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->post($marketplaceUrl . '/api/marketplace/modules/bulk-lookup', [
                    'slugs' => $slugs,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => $data['success'] ?? false,
                    'modules' => $data['data'] ?? [],
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'modules' => [],
                'error' => __('Marketplace returned status :status', ['status' => $response->status()]),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'modules' => [],
                'error' => __('Could not connect to the marketplace. You can skip this step and install modules later.'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'modules' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get locally available modules from the modules directory.
     * Used as fallback when marketplace API is unreachable.
     *
     * @param  array<string>  $slugs  Module slugs to look for
     * @return array<int, array>
     */
    public function getLocalModules(array $slugs): array
    {
        $modulesPath = config('modules.paths.modules', base_path('modules'));
        $modules = [];

        if (! is_dir($modulesPath)) {
            return [];
        }

        foreach (scandir($modulesPath) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $moduleJsonPath = $modulesPath . '/' . $folder . '/module.json';

            if (! file_exists($moduleJsonPath)) {
                continue;
            }

            $moduleData = json_decode(file_get_contents($moduleJsonPath), true);

            if (! is_array($moduleData)) {
                continue;
            }

            $slug = strtolower($moduleData['name'] ?? $folder);

            if (! in_array($slug, $slugs)) {
                continue;
            }

            $modules[] = [
                'slug' => $slug,
                'name' => $moduleData['title'] ?? $moduleData['name'] ?? $folder,
                'description' => $moduleData['description'] ?? '',
                'icons' => $moduleData['icon'] ?? null,
                'module_type' => 'free',
                'is_free' => true,
                'version' => $moduleData['version'] ?? '1.0.0',
                'download_url' => null,
                'is_local' => true,
            ];
        }

        return $modules;
    }

    /**
     * Download a module from the marketplace and install it.
     *
     * @return array{success: bool, message: string}
     */
    public function downloadAndInstallModule(string $slug, string $downloadUrl): array
    {
        try {
            $tempPath = storage_path('app/modules_temp/' . uniqid('install_', true));
            File::ensureDirectoryExists($tempPath);
            $zipPath = $tempPath . '/module.zip';

            // Download the zip
            $marketplaceUrl = rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');
            $appUrl = rtrim(config('app.url', ''), '/');

            if ($marketplaceUrl === $appUrl && ! empty($appUrl)) {
                // Local development - copy from storage
                $storagePath = $this->resolveLocalStoragePath($downloadUrl);

                if ($storagePath && File::exists($storagePath)) {
                    File::copy($storagePath, $zipPath);
                } else {
                    File::deleteDirectory($tempPath);

                    return ['success' => false, 'message' => __('Module file not found locally.')];
                }
            } else {
                // Remote download
                $response = Http::timeout(120)->sink($zipPath)->get($downloadUrl);

                if (! $response->successful()) {
                    File::deleteDirectory($tempPath);

                    return ['success' => false, 'message' => __('Failed to download module: :status', ['status' => $response->status()])];
                }
            }

            // Extract the zip
            $zip = new \ZipArchive();

            if (! $zip->open($zipPath)) {
                File::deleteDirectory($tempPath);

                return ['success' => false, 'message' => __('Failed to open module package.')];
            }

            $extractPath = $tempPath . '/extracted';
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // Find module.json in extracted content
            $moduleService = app(ModuleService::class);
            $modulesPath = config('modules.paths.modules', base_path('modules'));

            // Find the module folder (handles nested zip structures)
            $moduleFolder = $this->findModuleFolderInPath($extractPath);

            if (! $moduleFolder) {
                File::deleteDirectory($tempPath);

                return ['success' => false, 'message' => __('Invalid module package structure.')];
            }

            $moduleJsonPath = $moduleFolder . '/module.json';
            $moduleData = json_decode(File::get($moduleJsonPath), true);
            $folderName = basename($moduleFolder);
            $moduleName = $moduleData['name'] ?? $folderName;

            // Move to modules directory
            $targetPath = $modulesPath . '/' . $folderName;

            if (File::isDirectory($targetPath)) {
                // Module already exists locally, skip download
                File::deleteDirectory($tempPath);

                return ['success' => true, 'message' => __('Module already exists locally.')];
            }

            File::moveDirectory($moduleFolder, $targetPath);
            File::deleteDirectory($tempPath);

            // Set module status as disabled initially
            $moduleService->setModuleStatus($moduleName, false);

            Log::info("Module downloaded and installed during setup: {$moduleName}");

            return ['success' => true, 'message' => __('Module installed successfully.')];
        } catch (\Exception $e) {
            if (File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            Log::error("Failed to install module {$slug}: " . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Enable a module by name.
     */
    public function enableModule(string $moduleName): bool
    {
        try {
            $moduleService = app(ModuleService::class);
            $moduleService->toggleModule($moduleName, true);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable module {$moduleName}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Find the module folder containing module.json in an extracted path.
     */
    protected function findModuleFolderInPath(string $path): ?string
    {
        // Check if module.json is at root
        if (File::exists($path . '/module.json')) {
            return $path;
        }

        // Check subdirectories (one level deep)
        foreach (File::directories($path) as $dir) {
            if (File::exists($dir . '/module.json')) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Resolve a download URL to a local storage path for development mode.
     */
    protected function resolveLocalStoragePath(string $url): ?string
    {
        // Try to extract storage path from URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Handle /storage/ prefix
        if (str_contains($path, '/storage/')) {
            $storagePath = substr($path, strpos($path, '/storage/') + 9);

            return storage_path('app/public/' . $storagePath);
        }

        // Handle API download routes
        if (str_contains($path, '/api/modules/') && str_contains($path, '/download/')) {
            // This is an API route, not a direct file path
            return null;
        }

        return null;
    }
}
