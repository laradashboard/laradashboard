<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCrudCommand extends Command
{
    protected $signature = 'module:make-crud
                            {module : The name of the module}
                            {--migration= : The migration file name to parse columns from}
                            {--model= : The model name (if not parsing from migration)}
                            {--fields= : Field definitions (e.g., "title:string,content:text,featured_image:media,status:select:Active|Inactive,is_active:toggle")}';

    protected $description = 'Generate CRUD components (Model, Datatable, Create, Edit, Index) for a module';

    protected bool $migrationCreated = false;

    protected string $moduleName;

    protected string $moduleStudlyName;

    protected string $moduleLowerName;

    protected string $modulePath;

    protected string $modelName;

    protected string $modelStudlyName;

    protected string $modelLowerName;

    protected string $modelPluralName;

    protected string $modelPluralLower;

    protected string $tableName;

    protected array $columns = [];

    protected string $stubPath = 'stubs/laradashboard/crud';

    /**
     * Get the path to a stub file.
     */
    protected function getStubPath(string $stub): string
    {
        return base_path("{$this->stubPath}/{$stub}.stub");
    }

    /**
     * Get stub content with token replacements.
     */
    protected function getStub(string $stub): string
    {
        $path = $this->getStubPath($stub);

        if (! File::exists($path)) {
            throw new \RuntimeException("Stub file not found: {$path}");
        }

        return File::get($path);
    }

    /**
     * Get common token replacements.
     */
    protected function getTokenReplacements(): array
    {
        return [
            '$MODULE_NAMESPACE$' => 'Modules',
            '$STUDLY_NAME$' => $this->moduleStudlyName,
            '$LOWER_NAME$' => $this->moduleLowerName,
            '$MODEL_NAME$' => $this->modelStudlyName,
            '$MODEL_LOWER$' => $this->modelLowerName,
            '$MODEL_PLURAL$' => $this->modelPluralName,
            '$MODEL_PLURAL_LOWER$' => $this->modelPluralLower,
            '$TABLE_NAME$' => $this->tableName,
        ];
    }

    /**
     * Replace tokens in content.
     */
    protected function replaceStubTokens(string $content, array $additionalTokens = []): string
    {
        $tokens = array_merge($this->getTokenReplacements(), $additionalTokens);

        foreach ($tokens as $token => $value) {
            $content = str_replace($token, $value, $content);
        }

        return $content;
    }

    /**
     * Resolve the module path, trying both PascalCase and kebab-case formats.
     */
    protected function resolveModulePath(): ?string
    {
        // Try different naming conventions for the module directory
        $possiblePaths = [
            base_path("modules/{$this->moduleStudlyName}"),          // PascalCase: TestCrud
            base_path('modules/'.Str::kebab($this->moduleName)),     // kebab-case: test-crud
            base_path("modules/{$this->moduleLowerName}"),           // lowercase: testcrud
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                $this->modulePath = $path;

                return $path;
            }
        }

        return null;
    }

    public function handle(): int
    {
        $this->moduleName = $this->argument('module');
        $this->moduleStudlyName = Str::studly($this->moduleName);
        $this->moduleLowerName = Str::lower($this->moduleName);

        // Validate module exists - try both PascalCase and kebab-case paths
        $modulePath = $this->resolveModulePath();
        if (! $modulePath) {
            $this->error("Module '{$this->moduleStudlyName}' not found in modules/ directory.");
            $this->listAvailableModules();

            return self::FAILURE;
        }

        // Get model name from option or migration
        $migrationOption = $this->option('migration');
        $modelOption = $this->option('model');

        if (! $migrationOption && ! $modelOption) {
            $this->error('You must provide either --migration or --model option.');

            return self::FAILURE;
        }

        if ($migrationOption) {
            $this->parseMigration($migrationOption);
        } else {
            // Extract just the class name if a file path was provided
            $this->modelName = $modelOption;
            if (str_contains($this->modelName, '/') || str_contains($this->modelName, '\\')) {
                $this->modelName = pathinfo($this->modelName, PATHINFO_FILENAME);
                // Remove .php extension if present
                $this->modelName = preg_replace('/\.php$/i', '', $this->modelName);
            }
            $this->modelStudlyName = Str::studly($this->modelName);
            $this->modelLowerName = Str::lower($this->modelName);
            $this->modelPluralName = Str::plural($this->modelStudlyName);
            $this->modelPluralLower = Str::lower($this->modelPluralName);
            $this->tableName = $this->moduleLowerName.'_'.Str::snake($this->modelPluralName);

            // Try to auto-detect and parse migration for this model
            $this->tryAutoDetectMigration();
        }

        $this->info("Generating CRUD for {$this->modelStudlyName} in {$this->moduleStudlyName} module...");
        $this->newLine();

        // Generate files
        $this->generateModel();
        $this->generateDatatable();
        $this->generateIndexComponent();
        $this->generateShowComponent();
        $this->generateCreateComponent();
        $this->generateEditComponent();
        $this->generateViews();
        $this->updateRoutes();
        $this->updateMenuService();

        $this->newLine();
        $this->info('CRUD generated successfully!');
        $this->newLine();

        $this->comment('Files created:');
        $this->showGeneratedFiles();

        $this->newLine();
        $this->comment('Next steps:');
        if ($this->migrationCreated) {
            $this->line('  1. Run migrations to create the table: php artisan migrate');
        } else {
            $this->line('  1. Run migrations if the table does not exist: php artisan migrate');
        }
        $this->line('  2. Review and customize the generated Model fillable fields and casts');
        $this->line('  3. Update the Datatable headers and columns as needed');
        $this->line('  4. Customize the Create/Edit forms with appropriate input fields');
        $this->line('  5. Clear route cache: php artisan optimize:clear');

        // Show the URL where the page can be accessed
        $this->newLine();
        $this->comment('Access your new CRUD:');
        $routeName = "admin.{$this->moduleLowerName}.{$this->modelPluralLower}.index";
        try {
            $url = route($routeName);
            $this->info("  URL: {$url}");
        } catch (\Exception $e) {
            $this->line("  Route: {$routeName}");
            $this->line("  (Run 'php artisan optimize:clear' to refresh routes)");
        }

        return self::SUCCESS;
    }

    protected function parseMigration(string $migrationName): void
    {
        // Find migration file in module
        $migrationsPath = "{$this->modulePath}/database/migrations";
        $files = glob("{$migrationsPath}/*{$migrationName}*.php");

        if (empty($files)) {
            // Try in main migrations folder
            $migrationsPath = database_path('migrations');
            $files = glob("{$migrationsPath}/*{$migrationName}*.php");
        }

        if (empty($files)) {
            $this->error("Migration file containing '{$migrationName}' not found.");
            exit(self::FAILURE);
        }

        $migrationFile = $files[0];
        $this->info("Parsing migration: ".basename($migrationFile));

        $content = file_get_contents($migrationFile);

        // Extract table name from Schema::create
        if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            $this->tableName = $matches[1];
        } else {
            $this->error('Could not find table name in migration file.');
            exit(self::FAILURE);
        }

        // Extract model name from table name
        // Remove module prefix if exists (e.g., docforge_categories -> categories -> Category)
        $tableWithoutPrefix = preg_replace("/^{$this->moduleLowerName}_/", '', $this->tableName);
        $this->modelStudlyName = Str::studly(Str::singular($tableWithoutPrefix));
        $this->modelName = $this->modelStudlyName;
        $this->modelLowerName = Str::lower($this->modelStudlyName);
        $this->modelPluralName = Str::plural($this->modelStudlyName);
        $this->modelPluralLower = Str::lower($this->modelPluralName);

        // Parse columns from migration
        $this->parseColumns($content);
    }

    protected function tryAutoDetectMigration(): void
    {
        // Try to find migration for this model
        // Look for patterns like: create_books_table, create_sample_books_table
        $snakePlural = Str::snake($this->modelPluralName);
        $patterns = [
            "create_{$this->moduleLowerName}_{$snakePlural}_table",
            "create_{$snakePlural}_table",
        ];

        $migrationsPath = "{$this->modulePath}/database/migrations";
        $migrationFile = null;

        foreach ($patterns as $pattern) {
            $files = glob("{$migrationsPath}/*{$pattern}*.php");
            if (! empty($files)) {
                $migrationFile = $files[0];
                break;
            }
        }

        // Also try main migrations folder
        if (! $migrationFile) {
            $migrationsPath = database_path('migrations');
            foreach ($patterns as $pattern) {
                $files = glob("{$migrationsPath}/*{$pattern}*.php");
                if (! empty($files)) {
                    $migrationFile = $files[0];
                    break;
                }
            }
        }

        $fieldsOption = $this->option('fields');

        // If --fields option is provided, always use it (takes precedence over migration parsing)
        if ($fieldsOption) {
            $this->parseFieldsOption($fieldsOption);

            if ($migrationFile) {
                $this->info('Auto-detected migration: '.basename($migrationFile));
                $content = file_get_contents($migrationFile);

                // Extract table name from Schema::create
                if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                    $this->tableName = $matches[1];
                }
            } else {
                // No migration found, create one
                $this->generateMigration();
            }
        } elseif ($migrationFile) {
            $this->info('Auto-detected migration: '.basename($migrationFile));
            $content = file_get_contents($migrationFile);

            // Extract table name from Schema::create
            if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                $this->tableName = $matches[1];
            }

            // Parse columns from migration
            $this->parseColumns($content);
        } else {
            // No migration found and no --fields option - prompt for fields
            $this->info("No migration found for {$this->modelStudlyName}.");
            $this->newLine();

            if ($this->confirm('Would you like to define fields and create a migration?', true)) {
                $this->promptForFields();
                $this->generateMigration();
            } else {
                $this->warn("Using default 'name' field.");
                $this->warn("Tip: Use --fields option (e.g., --fields=\"title:string,content:text\")");
            }
        }
    }

    protected function parseFieldsOption(string $fieldsOption): void
    {
        $this->columns = [];
        $fields = explode(',', $fieldsOption);

        foreach ($fields as $field) {
            $field = trim($field);
            if (empty($field)) {
                continue;
            }

            $parts = explode(':', $field);
            $name = trim($parts[0]);
            $type = isset($parts[1]) ? trim($parts[1]) : 'string';
            $options = isset($parts[2]) ? trim($parts[2]) : null;

            // Skip id and timestamps
            if (\in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $column = [
                'name' => $name,
                'type' => $this->mapColumnType($type),
                'dbType' => $this->mapDbType($type),
            ];

            // Handle select options (e.g., select:Active|Inactive|Pending)
            if ($type === 'select' && $options) {
                $column['options'] = explode('|', $options);
            }

            $this->columns[] = $column;
        }
    }

    /**
     * Map user-friendly types to database column types.
     */
    protected function mapDbType(string $type): string
    {
        return match ($type) {
            'toggle' => 'boolean',
            'editor' => 'text',
            'media' => 'foreignId',
            'file' => 'string',
            'select' => 'string',
            default => $type,
        };
    }

    protected function promptForFields(): void
    {
        $this->columns = [];
        $this->info('Define your fields (press Enter with empty name to finish):');
        $this->line('  Basic types: string, text, integer, boolean, date, datetime, decimal, json');
        $this->line('  UI types: toggle (switch), select (dropdown), editor (rich text), media (media library)');
        $this->newLine();

        $fieldNumber = 1;
        while (true) {
            $name = $this->ask("Field {$fieldNumber} name (or press Enter to finish)");

            if (empty($name)) {
                break;
            }

            // Validate field name
            $name = Str::snake(trim($name));

            if (\in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $this->warn("  '{$name}' is auto-generated, skipping...");

                continue;
            }

            $type = $this->choice(
                "Field {$fieldNumber} type for '{$name}'",
                ['string', 'text', 'integer', 'boolean', 'toggle', 'select', 'editor', 'media', 'date', 'datetime', 'decimal', 'json'],
                0
            );

            $column = [
                'name' => $name,
                'type' => $this->mapColumnType($type),
                'dbType' => $this->mapDbType($type),
            ];

            // If select, ask for options
            if ($type === 'select') {
                $optionsInput = $this->ask("  Enter options separated by | (e.g., Active|Inactive|Pending)", '');
                if ($optionsInput) {
                    $column['options'] = explode('|', $optionsInput);
                }
            }

            $this->columns[] = $column;

            $this->info("  Added: {$name} ({$type})");
            $fieldNumber++;
        }

        if (empty($this->columns)) {
            $this->warn('No fields defined, using default "name" field.');
            $this->columns[] = [
                'name' => 'name',
                'type' => 'text',
                'dbType' => 'string',
            ];
        }
    }

    protected function generateMigration(): void
    {
        $migrationName = "create_{$this->tableName}_table";
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";

        $migrationsPath = "{$this->modulePath}/database/migrations";
        $this->ensureDirectoryExists($migrationsPath);

        $path = "{$migrationsPath}/{$filename}";

        // Get stub and replace tokens
        $content = $this->getStub('migration');
        $content = $this->replaceStubTokens($content, [
            '$MIGRATION_COLUMNS$' => $this->generateMigrationColumns(),
        ]);

        File::put($path, $content);
        $this->migrationCreated = true;
        $this->info("  Created migration: {$filename}");
    }

    protected function generateMigrationColumns(): string
    {
        $lines = [];

        foreach ($this->columns as $column) {
            // Media fields use foreign key to media table
            if ($column['type'] === 'media') {
                $columnName = $this->getMediaColumnName($column['name']);
                $lines[] = "            \$table->foreignId('{$columnName}')->nullable()->constrained('media')->nullOnDelete();";

                continue;
            }

            $method = match ($column['dbType']) {
                'string' => 'string',
                'text' => 'text',
                'integer' => 'integer',
                'boolean' => 'boolean',
                'date' => 'date',
                'datetime' => 'dateTime',
                'decimal' => 'decimal',
                'json' => 'json',
                default => 'string',
            };

            // File columns should be nullable (stored as string paths)
            $isNullable = in_array($column['dbType'], ['text', 'json', 'date', 'datetime']) || $column['type'] === 'file';
            $nullable = $isNullable ? '->nullable()' : '';
            $default = $column['dbType'] === 'boolean' ? '->default(false)' : '';

            $lines[] = "            \$table->{$method}('{$column['name']}'){$nullable}{$default};";
        }

        return implode("\n", $lines);
    }

    /**
     * Get the database column name for a media field.
     * Adds '_id' suffix if not already present.
     */
    protected function getMediaColumnName(string $fieldName): string
    {
        return str_ends_with($fieldName, '_id') ? $fieldName : $fieldName.'_id';
    }

    protected function parseColumns(string $content): void
    {
        $this->columns = [];

        // Match column definitions like $table->string('name'), $table->text('description'), etc.
        $pattern = '/\$table->(\w+)\s*\(\s*[\'"](\w+)[\'"]/';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $type = $match[1];
                $name = $match[2];

                // Skip certain columns
                if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }

                // Skip relation columns (we'll handle them separately)
                if (str_ends_with($name, '_id') && $type === 'foreignId') {
                    continue;
                }

                $this->columns[] = [
                    'name' => $name,
                    'type' => $this->mapColumnType($type),
                    'dbType' => $type,
                ];
            }
        }

        // Also extract foreign keys
        $foreignPattern = '/\$table->foreignId\s*\(\s*[\'"](\w+)[\'"]/';
        if (preg_match_all($foreignPattern, $content, $matches)) {
            foreach ($matches[1] as $foreignKey) {
                $this->columns[] = [
                    'name' => $foreignKey,
                    'type' => 'select',
                    'dbType' => 'foreignId',
                    'isForeign' => true,
                ];
            }
        }
    }

    protected function mapColumnType(string $dbType): string
    {
        return match ($dbType) {
            'string', 'char' => 'text',
            'text', 'mediumText', 'longText' => 'textarea',
            'integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger' => 'number',
            'decimal', 'float', 'double' => 'number',
            'boolean' => 'checkbox',
            'toggle' => 'toggle',
            'select' => 'select',
            'editor' => 'editor',
            'media' => 'media',
            'file' => 'file',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'json', 'jsonb' => 'textarea',
            default => 'text',
        };
    }

    protected function generateModel(): void
    {
        $path = "{$this->modulePath}/app/Models/{$this->modelStudlyName}.php";

        // Skip if model already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Models/{$this->modelStudlyName}.php (already exists)");

            return;
        }

        // Generate dynamic content
        $fillable = $this->generateFillableArray();
        $casts = $this->generateCastsArray();
        $hasMedia = $this->hasMediaFields();
        $mediaImports = $hasMedia ? "use App\\Models\\Media;\nuse Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;\n" : '';
        $mediaRelationships = $this->generateMediaRelationships();

        // Get stub and replace tokens
        $content = $this->getStub('model');
        $content = $this->replaceStubTokens($content, [
            '$FILLABLE$' => $fillable,
            '$CASTS$' => $casts,
            '$MODEL_IMPORTS$' => $mediaImports,
            '$MODEL_RELATIONSHIPS$' => $mediaRelationships,
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Models/{$this->modelStudlyName}.php");
    }

    /**
     * Check if any fields are media type.
     */
    protected function hasMediaFields(): bool
    {
        foreach ($this->columns as $column) {
            if ($column['type'] === 'media') {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate media relationship methods for the model.
     */
    protected function generateMediaRelationships(): string
    {
        $relationships = [];

        foreach ($this->columns as $column) {
            if ($column['type'] !== 'media') {
                continue;
            }

            $methodName = Str::camel($column['name']);
            $studlyMethodName = Str::studly($column['name']);
            $columnName = $this->getMediaColumnName($column['name']);

            $relationships[] = <<<PHP

    /**
     * Get the {$column['name']} media.
     */
    public function {$methodName}(): BelongsTo
    {
        return \$this->belongsTo(Media::class, '{$columnName}');
    }

    /**
     * Get the {$column['name']} URL.
     */
    public function get{$studlyMethodName}UrlAttribute(): ?string
    {
        return \$this->{$columnName} && \$this->{$methodName}
            ? asset('storage/media/' . \$this->{$methodName}->file_name)
            : null;
    }
PHP;
        }

        return implode("\n", $relationships);
    }

    protected function generateDatatable(): void
    {
        $path = "{$this->modulePath}/app/Livewire/Components/{$this->modelStudlyName}Datatable.php";

        // Skip if datatable already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Components/{$this->modelStudlyName}Datatable.php (already exists)");

            return;
        }

        // Get stub and replace tokens
        $content = $this->getStub('datatable');
        $content = $this->replaceStubTokens($content, [
            '$HEADERS$' => $this->generateDatatableHeaders(),
            '$SEARCH_QUERY$' => $this->generateSearchQuery(),
            '$RENDER_METHODS$' => $this->generateDatatableRenderMethods(),
            '$ADDITIONAL_IMPORTS$' => $this->generateDatatableAdditionalImports(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Components/{$this->modelStudlyName}Datatable.php");
    }

    protected function generateDatatableRenderMethods(): string
    {
        $methods = [];

        foreach ($this->columns as $column) {
            $methodName = 'render'.Str::studly($column['name']).'Column';

            if ($column['type'] === 'file') {
                $methods[] = <<<PHP

    public function {$methodName}({$this->modelStudlyName} \$item): Renderable
    {
        \$path = \$item->{$column['name']};

        if (! \$path) {
            return view('components.datatable.empty-cell');
        }

        \$url = asset('storage/' . \$path);
        \$isImage = preg_match('/\\.(jpg|jpeg|png|gif|webp|svg)\$/i', \$path);

        if (\$isImage) {
            return view('components.datatable.image-cell', ['url' => \$url, 'alt' => \$item->title ?? '']);
        }

        return view('components.datatable.file-cell', ['url' => \$url, 'filename' => basename(\$path)]);
    }
PHP;
            } elseif ($column['type'] === 'media') {
                $columnName = $this->getMediaColumnName($column['name']);
                $relationName = Str::camel($column['name']);
                $methods[] = <<<PHP

    public function {$methodName}({$this->modelStudlyName} \$item): Renderable
    {
        if (! \$item->{$columnName} || ! \$item->{$relationName}) {
            return view('components.datatable.empty-cell');
        }

        \$url = \$item->{$relationName}Url;

        return view('components.datatable.image-cell', ['url' => \$url, 'alt' => \$item->title ?? '']);
    }
PHP;
            } elseif ($column['type'] === 'checkbox' || $column['type'] === 'toggle') {
                $methods[] = <<<PHP

    public function {$methodName}({$this->modelStudlyName} \$item): Renderable
    {
        return view('components.datatable.boolean-cell', ['value' => \$item->{$column['name']}]);
    }
PHP;
            }
        }

        return implode("\n", $methods);
    }

    /**
     * Generate additional imports for the datatable.
     * Adds Renderable import if any columns need render methods.
     */
    protected function generateDatatableAdditionalImports(): string
    {
        $needsRenderable = false;

        foreach ($this->columns as $column) {
            if (in_array($column['type'], ['file', 'media', 'checkbox', 'toggle'])) {
                $needsRenderable = true;
                break;
            }
        }

        if ($needsRenderable) {
            return "use Illuminate\\Contracts\\Support\\Renderable;\n";
        }

        return '';
    }

    protected function generateIndexComponent(): void
    {
        $path = "{$this->modulePath}/app/Livewire/Admin/{$this->modelPluralName}/Index.php";

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Index.php (already exists)");

            return;
        }

        $content = $this->getStub('components/index');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Index.php");
    }

    protected function generateShowComponent(): void
    {
        $path = "{$this->modulePath}/app/Livewire/Admin/{$this->modelPluralName}/Show.php";

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Show.php (already exists)");

            return;
        }

        $content = $this->getStub('components/show');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Show.php");
    }

    protected function generateCreateComponent(): void
    {
        $path = "{$this->modulePath}/app/Livewire/Admin/{$this->modelPluralName}/Create.php";

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Create.php (already exists)");

            return;
        }

        // Generate dynamic content for file uploads
        $hasFiles = $this->hasFileFields();
        $useStatements = $hasFiles ? "use Livewire\\WithFileUploads;\n" : '';
        $traitUse = $hasFiles ? "    use WithFileUploads;\n\n" : '';
        $fileDeleteMethods = $hasFiles ? $this->generateFileDeleteMethods() : '';

        $content = $this->getStub('components/create');
        $content = $this->replaceStubTokens($content, [
            '$USE_STATEMENTS$' => $useStatements,
            '$TRAIT_USE$' => $traitUse,
            '$PROPERTIES$' => $this->generateFormProperties(),
            '$RULES$' => $this->generateValidationRules(),
            '$CREATE_DATA$' => $this->generateCreateData(),
            '$FILE_DELETE_METHODS$' => $fileDeleteMethods,
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Create.php");
    }

    protected function generateEditComponent(): void
    {
        $path = "{$this->modulePath}/app/Livewire/Admin/{$this->modelPluralName}/Edit.php";

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Edit.php (already exists)");

            return;
        }

        // Generate dynamic content for file uploads
        $hasFiles = $this->hasFileFields();
        $useStatements = $hasFiles ? "use Livewire\\WithFileUploads;\n" : '';
        $traitUse = $hasFiles ? "    use WithFileUploads;\n\n" : '';

        $content = $this->getStub('components/edit');
        $content = $this->replaceStubTokens($content, [
            '$USE_STATEMENTS$' => $useStatements,
            '$TRAIT_USE$' => $traitUse,
            '$PROPERTIES$' => $this->generateFormProperties(),
            '$RULES$' => $this->generateValidationRules(),
            '$MOUNT_ASSIGNMENTS$' => $this->generateMountAssignments(),
            '$UPDATE_DATA$' => $this->generateUpdateData(),
            '$FILE_DELETE_METHODS$' => $this->generateFileDeleteMethods(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Edit.php");
    }

    protected function generateViews(): void
    {
        $this->generateCrudLayout();
        $this->generateIndexView();
        $this->generateShowView();
        $this->generateCreateView();
        $this->generateEditView();
    }

    protected function generateCrudLayout(): void
    {
        $path = "{$this->modulePath}/resources/views/layouts/crud.blade.php";

        // Skip if layout already exists
        if (File::exists($path)) {
            $this->line('  Skipped: views/layouts/crud.blade.php (already exists)');

            return;
        }

        $content = $this->getStub('layout');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info('  Created: views/layouts/crud.blade.php');
    }

    protected function generateIndexView(): void
    {
        $path = "{$this->modulePath}/resources/views/livewire/admin/{$this->modelPluralLower}/index.blade.php";

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/index.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/index');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/index.blade.php");
    }

    protected function generateShowView(): void
    {
        $path = "{$this->modulePath}/resources/views/livewire/admin/{$this->modelPluralLower}/show.blade.php";

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/show.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/show');
        $content = $this->replaceStubTokens($content, [
            '$DISPLAY_FIELDS$' => $this->generateDisplayFields(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/show.blade.php");
    }

    protected function generateCreateView(): void
    {
        $path = "{$this->modulePath}/resources/views/livewire/admin/{$this->modelPluralLower}/create.blade.php";

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/create.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/create');
        $content = $this->replaceStubTokens($content, [
            '$FORM_FIELDS$' => $this->generateFormFields(),
            '$EDITOR_ASSETS$' => $this->generateEditorAssets(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/create.blade.php");
    }

    protected function generateEditView(): void
    {
        $path = "{$this->modulePath}/resources/views/livewire/admin/{$this->modelPluralLower}/edit.blade.php";

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/edit.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/edit');
        $content = $this->replaceStubTokens($content, [
            '$FORM_FIELDS$' => $this->generateFormFields(),
            '$EDITOR_ASSETS$' => $this->generateEditorAssets(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/edit.blade.php");
    }

    protected function updateRoutes(): void
    {
        $routesPath = "{$this->modulePath}/routes/web.php";

        if (! File::exists($routesPath)) {
            $this->warn("  Routes file not found: {$routesPath}");

            return;
        }

        $content = File::get($routesPath);

        // Check if routes already exist
        if (str_contains($content, "admin.{$this->moduleLowerName}.{$this->modelPluralLower}")) {
            $this->line("  Routes already exist, skipping...");

            return;
        }

        // Add use statements
        $useStatements = "use Modules\\{$this->moduleStudlyName}\\Livewire\\Admin\\{$this->modelPluralName}\\Create as {$this->modelStudlyName}Create;\n"
            ."use Modules\\{$this->moduleStudlyName}\\Livewire\\Admin\\{$this->modelPluralName}\\Edit as {$this->modelStudlyName}Edit;\n"
            ."use Modules\\{$this->moduleStudlyName}\\Livewire\\Admin\\{$this->modelPluralName}\\Index as {$this->modelStudlyName}Index;\n"
            ."use Modules\\{$this->moduleStudlyName}\\Livewire\\Admin\\{$this->modelPluralName}\\Show as {$this->modelStudlyName}Show;";

        // Find the last use statement and add after it
        if (preg_match_all('/^use [^;]+;$/m', $content, $matches)) {
            $lastUseStatement = end($matches[0]);
            $lastPos = strrpos($content, $lastUseStatement);
            $insertPos = $lastPos + strlen($lastUseStatement);
            $content = substr_replace($content, "\n".$useStatements, $insertPos, 0);
        }

        // Add routes before closing of the group
        $newRoutes = "\n\n        Route::get('{$this->modelPluralLower}', {$this->modelStudlyName}Index::class)->name('{$this->modelPluralLower}.index');\n"
            ."        Route::get('{$this->modelPluralLower}/create', {$this->modelStudlyName}Create::class)->name('{$this->modelPluralLower}.create');\n"
            ."        Route::get('{$this->modelPluralLower}/{{$this->modelLowerName}}', {$this->modelStudlyName}Show::class)->name('{$this->modelPluralLower}.show');\n"
            ."        Route::get('{$this->modelPluralLower}/{{$this->modelLowerName}}/edit', {$this->modelStudlyName}Edit::class)->name('{$this->modelPluralLower}.edit');";

        // Find the first }); that closes the route group and insert before it
        $content = preg_replace('/(\n\s*}\s*\)\s*;)/', $newRoutes.'$1', $content, 1);

        File::put($routesPath, $content);
        $this->info("  Updated: routes/web.php");
    }

    protected function updateMenuService(): void
    {
        // Try to find the MenuService file
        $menuServicePath = "{$this->modulePath}/app/Services/MenuService.php";

        // Also check for module-specific naming
        if (! File::exists($menuServicePath)) {
            $menuServicePath = "{$this->modulePath}/app/Services/{$this->moduleStudlyName}MenuService.php";
        }

        if (! File::exists($menuServicePath)) {
            $this->line('  MenuService not found, skipping menu update...');
            $this->line("  You can manually add a menu item for {$this->modelPluralName}");

            return;
        }

        $content = File::get($menuServicePath);

        // Check if menu item already exists
        if (str_contains($content, "'{$this->moduleLowerName}-{$this->modelPluralLower}'") ||
            str_contains($content, "admin.{$this->moduleLowerName}.{$this->modelPluralLower}.index")) {
            $this->line('  Menu item already exists, skipping...');

            return;
        }

        // Build the submenu code block
        $submenuCode = <<<PHP

        // {$this->modelPluralName} submenu
        \$menu->setChildren(array_merge(\$menu->children, [
            (new AdminMenuItem())->setAttributes([
                'label' => __('{$this->modelPluralName}'),
                'icon' => 'lucide:list',
                'route' => route('admin.{$this->moduleLowerName}.{$this->modelPluralLower}.index'),
                'active' => Route::is('admin.{$this->moduleLowerName}.{$this->modelPluralLower}.*'),
                'id' => '{$this->moduleLowerName}-{$this->modelPluralLower}',
                'permissions' => [],
            ]),
        ]));
PHP;

        // Insert before "return $menu;" in the getMenu method
        if (preg_match('/return\s+\$menu\s*;/', $content)) {
            $content = preg_replace(
                '/(return\s+\$menu\s*;)/',
                $submenuCode."\n\n        $1",
                $content,
                1
            );

            File::put($menuServicePath, $content);
            $this->info('  Updated: MenuService with submenu item');
        } else {
            $this->line('  Could not update MenuService automatically');
            $this->line("  Please add menu item for {$this->modelPluralName} manually");
        }
    }

    protected function replaceTokens(string $content): string
    {
        return str_replace(
            [
                '$MODULE_NAMESPACE$',
                '$STUDLY_NAME$',
                '$LOWER_NAME$',
                '$MODEL_NAME$',
                '$MODEL_LOWER$',
                '$MODEL_PLURAL$',
                '$MODEL_PLURAL_LOWER$',
                '$TABLE_NAME$',
            ],
            [
                'Modules',
                $this->moduleStudlyName,
                $this->moduleLowerName,
                $this->modelStudlyName,
                $this->modelLowerName,
                $this->modelPluralName,
                $this->modelPluralLower,
                $this->tableName,
            ],
            $content
        );
    }

    protected function generateFillableArray(): string
    {
        if (empty($this->columns)) {
            return "'name',";
        }

        $fillable = [];
        foreach ($this->columns as $column) {
            // Media fields use _id suffix in database
            $columnName = $column['type'] === 'media'
                ? $this->getMediaColumnName($column['name'])
                : $column['name'];
            $fillable[] = "'{$columnName}'";
        }

        return implode(",\n        ", $fillable).',';
    }

    protected function generateCastsArray(): string
    {
        $casts = [];
        foreach ($this->columns as $column) {
            if ($column['dbType'] === 'boolean') {
                $casts[] = "'{$column['name']}' => 'boolean'";
            } elseif (in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger'])) {
                $casts[] = "'{$column['name']}' => 'integer'";
            } elseif (in_array($column['dbType'], ['json', 'jsonb'])) {
                $casts[] = "'{$column['name']}' => 'array'";
            } elseif ($column['dbType'] === 'date') {
                $casts[] = "'{$column['name']}' => 'date'";
            } elseif (in_array($column['dbType'], ['datetime', 'timestamp'])) {
                $casts[] = "'{$column['name']}' => 'datetime'";
            }
        }

        if (empty($casts)) {
            return '// Add casts here';
        }

        return implode(",\n            ", $casts).',';
    }

    protected function generateDatatableHeaders(): string
    {
        $headers = [];

        if (empty($this->columns)) {
            // Default to name column
            $headers[] = <<<'PHP'
[
                'id' => 'name',
                'title' => __('Name'),
                'sortable' => true,
                'sortBy' => 'name',
                'searchable' => true,
            ]
PHP;
        } else {
            // Generate headers from parsed columns
            foreach ($this->columns as $column) {
                $label = Str::title(str_replace('_', ' ', $column['name']));
                $isSearchable = in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText']);
                $searchableStr = $isSearchable ? "\n                'searchable' => true," : '';
                $widthStr = '';

                // Add width for specific column types
                if (in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'tinyInteger', 'smallInteger', 'boolean', 'date'])) {
                    $widthStr = "\n                'width' => '120px',";
                }

                // Media fields use the original name for the header ID (render method naming)
                // but the sortBy should use the _id column
                $headerId = $column['name'];
                $sortBy = $column['type'] === 'media'
                    ? $this->getMediaColumnName($column['name'])
                    : $column['name'];

                $headers[] = <<<PHP
[
                'id' => '{$headerId}',
                'title' => __('{$label}'),
                'sortable' => true,
                'sortBy' => '{$sortBy}',{$searchableStr}{$widthStr}
            ]
PHP;
            }
        }

        // Add created_at
        $headers[] = <<<'PHP'
[
                'id' => 'created_at',
                'title' => __('Created'),
                'sortable' => true,
                'sortBy' => 'created_at',
                'width' => '150px',
            ]
PHP;

        // Add actions
        $headers[] = <<<'PHP'
[
                'id' => 'actions',
                'title' => __('Actions'),
                'sortable' => false,
                'is_action' => true,
                'width' => '100px',
            ]
PHP;

        return implode(",\n            ", $headers).',';
    }

    protected function generateSearchQuery(): string
    {
        $searchableColumns = [];

        if (empty($this->columns)) {
            $searchableColumns[] = 'name';
        } else {
            foreach ($this->columns as $column) {
                // Only text-based columns should be searchable
                if (in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])) {
                    $searchableColumns[] = $column['name'];
                }
            }
        }

        if (empty($searchableColumns)) {
            // If no searchable columns, use the first column
            $searchableColumns[] = $this->columns[0]['name'] ?? 'id';
        }

        $conditions = [];
        foreach ($searchableColumns as $index => $columnName) {
            if ($index === 0) {
                $conditions[] = "\$q->where('{$columnName}', 'like', \"%{\$this->search}%\")";
            } else {
                $conditions[] = "                        ->orWhere('{$columnName}', 'like', \"%{\$this->search}%\")";
            }
        }

        return implode("\n", $conditions).';';
    }

    protected function generateFormProperties(): string
    {
        if (empty($this->columns)) {
            return "public string \$name = '';";
        }

        $properties = [];
        foreach ($this->columns as $column) {
            $type = $this->getPhpPropertyType($column);
            $default = $this->getPropertyDefault($column);

            // File types need untyped nullable declaration for Livewire
            if ($column['type'] === 'file') {
                $properties[] = "public \${$column['name']} = null;";
            } elseif ($column['type'] === 'media') {
                // Media fields store the media ID (nullable integer)
                $propertyName = $this->getMediaColumnName($column['name']);
                $properties[] = "public ?int \${$propertyName} = null;";
            } else {
                $properties[] = "public {$type} \${$column['name']} = {$default};";
            }
        }

        return implode("\n\n    ", $properties);
    }

    protected function hasFileFields(): bool
    {
        foreach ($this->columns as $column) {
            if ($column['type'] === 'file') {
                return true;
            }
        }

        return false;
    }

    protected function getPhpPropertyType(array $column): string
    {
        // Check dbType for decimals which need float, not int
        $dbType = $column['dbType'] ?? '';
        if (in_array($dbType, ['decimal', 'float', 'double'])) {
            return 'float';
        }

        return match ($column['type']) {
            'number' => 'int',
            'checkbox', 'toggle' => 'bool',
            'file' => 'mixed', // Can be UploadedFile or null
            default => 'string',
        };
    }

    protected function getPropertyDefault(array $column): string
    {
        // Check dbType for decimals
        $dbType = $column['dbType'] ?? '';
        if (in_array($dbType, ['decimal', 'float', 'double'])) {
            return '0.0';
        }

        return match ($column['type']) {
            'number' => '0',
            'checkbox', 'toggle' => 'false',
            'file' => 'null',
            default => "''",
        };
    }

    protected function generateValidationRules(): string
    {
        if (empty($this->columns)) {
            return "'name' => 'required|string|max:255',";
        }

        $rules = [];
        foreach ($this->columns as $column) {
            $rule = $this->getValidationRule($column);
            // File fields use array format for better Livewire compatibility
            if ($column['type'] === 'file') {
                $rules[] = "'{$column['name']}' => ['nullable', 'file', 'max:10240']";
            } elseif ($column['type'] === 'media') {
                // Media fields store the media ID (nullable integer, must exist in media table)
                $propertyName = $this->getMediaColumnName($column['name']);
                $rules[] = "'{$propertyName}' => 'nullable|integer|exists:media,id'";
            } else {
                $rules[] = "'{$column['name']}' => '{$rule}'";
            }
        }

        return implode(",\n            ", $rules).',';
    }

    protected function getValidationRule(array $column): string
    {
        $rules = [];

        // Determine if required based on column type
        $isRequired = \in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || \in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger']);

        // File fields are typically nullable
        if ($column['type'] === 'file') {
            $isRequired = false;
        }

        $rules[] = $isRequired ? 'required' : 'nullable';

        // Add type-specific rules
        match ($column['type']) {
            'text' => $rules[] = 'string|max:255',
            'textarea' => $rules[] = 'string|max:1000',
            'editor' => $rules[] = 'string|max:65535',
            'number' => $rules[] = 'integer|min:0',
            'checkbox', 'toggle' => $rules[] = 'boolean',
            'date' => $rules[] = 'date',
            'datetime' => $rules[] = 'date',
            'select' => $rules[] = 'string|max:255',
            'file' => $rules[] = 'file|max:10240', // 10MB max
            default => $rules[] = 'string',
        };

        return implode('|', $rules);
    }

    protected function generateCreateData(): string
    {
        if (empty($this->columns)) {
            return "'name' => \$validated['name'],";
        }

        $data = [];

        foreach ($this->columns as $column) {
            if ($column['type'] === 'file') {
                // File fields: store and save path
                $data[] = "'{$column['name']}' => \$this->{$column['name']} ? \$this->{$column['name']}->store('{$this->modelPluralLower}', 'public') : null";
            } elseif ($column['type'] === 'media') {
                // Media fields: store the media ID
                $propertyName = $this->getMediaColumnName($column['name']);
                $data[] = "'{$propertyName}' => \$validated['{$propertyName}']";
            } else {
                $data[] = "'{$column['name']}' => \$validated['{$column['name']}']";
            }
        }

        return implode(",\n            ", $data).',';
    }

    protected function generateUpdateData(): string
    {
        if (empty($this->columns)) {
            return "'name' => \$validated['name'],";
        }

        $data = [];

        foreach ($this->columns as $column) {
            if ($column['type'] === 'file') {
                // File fields: only update if new file uploaded
                $data[] = "'{$column['name']}' => \$this->{$column['name']} ? \$this->{$column['name']}->store('{$this->modelPluralLower}', 'public') : \$this->{$this->modelLowerName}->{$column['name']}";
            } elseif ($column['type'] === 'media') {
                // Media fields: store the media ID
                $propertyName = $this->getMediaColumnName($column['name']);
                $data[] = "'{$propertyName}' => \$validated['{$propertyName}']";
            } else {
                $data[] = "'{$column['name']}' => \$validated['{$column['name']}']";
            }
        }

        return implode(",\n            ", $data).',';
    }

    protected function generateFileDeleteMethods(): string
    {
        $methods = [];

        foreach ($this->columns as $column) {
            if ($column['type'] !== 'file') {
                continue;
            }

            $methodName = 'delete'.Str::studly($column['name']);
            $label = Str::title(str_replace('_', ' ', $column['name']));

            $methods[] = <<<PHP

    public function {$methodName}(): void
    {
        if (\$this->{$this->modelLowerName}->{$column['name']}) {
            Storage::disk('public')->delete(\$this->{$this->modelLowerName}->{$column['name']});
            \$this->{$this->modelLowerName}->update(['{$column['name']}' => null]);
            session()->flash('success', __('{$label} deleted successfully.'));
        }
    }
PHP;
        }

        return implode("\n", $methods);
    }

    protected function generateMountAssignments(): string
    {
        if (empty($this->columns)) {
            return "\$this->name = \$this->{$this->modelLowerName}->name;";
        }

        $assignments = [];
        foreach ($this->columns as $column) {
            // Skip file fields - they don't get populated from existing model
            if ($column['type'] === 'file') {
                continue;
            }

            $dbType = $column['dbType'] ?? '';

            if ($column['type'] === 'media') {
                // Media fields use _id suffix
                $propertyName = $this->getMediaColumnName($column['name']);
                $assignments[] = "\$this->{$propertyName} = \$this->{$this->modelLowerName}->{$propertyName};";
            } elseif ($column['type'] === 'checkbox' || $column['type'] === 'toggle') {
                // Boolean fields need explicit cast to avoid int-to-bool type error
                $assignments[] = "\$this->{$column['name']} = (bool) \$this->{$this->modelLowerName}->{$column['name']};";
            } elseif (\in_array($column['type'], ['text', 'textarea', 'editor', 'select'])) {
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']} ?? '';";
            } elseif (in_array($dbType, ['decimal', 'float', 'double'])) {
                // Decimal/float fields need cast and null fallback
                $assignments[] = "\$this->{$column['name']} = (float) (\$this->{$this->modelLowerName}->{$column['name']} ?? 0);";
            } elseif ($column['type'] === 'number') {
                // Integer fields need null fallback
                $assignments[] = "\$this->{$column['name']} = (int) (\$this->{$this->modelLowerName}->{$column['name']} ?? 0);";
            } elseif ($column['type'] === 'date') {
                // Date fields - format as Y-m-d for date input
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']}?->format('Y-m-d') ?? '';";
            } elseif ($column['type'] === 'datetime') {
                // Datetime fields - format as Y-m-d\TH:i for datetime-local input
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']}?->format('Y-m-d\\TH:i') ?? '';";
            } elseif ($column['type'] === 'time') {
                // Time fields - format as H:i for time input
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']}?->format('H:i') ?? '';";
            } else {
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']};";
            }
        }

        return implode("\n        ", $assignments);
    }

    protected function generateFormFields(): string
    {
        if (empty($this->columns)) {
            return $this->generateDefaultFormField();
        }

        $fields = [];
        foreach ($this->columns as $column) {
            $fields[] = $this->generateFormFieldForColumn($column);
        }

        return implode("\n\n                ", $fields);
    }

    protected function generateDisplayFields(): string
    {
        if (empty($this->columns)) {
            return $this->generateDefaultDisplayField();
        }

        $fields = [];
        foreach ($this->columns as $column) {
            $fields[] = $this->generateDisplayFieldForColumn($column);
        }

        return implode("\n\n                ", $fields);
    }

    protected function generateDefaultDisplayField(): string
    {
        return <<<'BLADE'
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->MODEL_LOWER$->name }}</dd>
                </div>
BLADE;
    }

    protected function generateDisplayFieldForColumn(array $column): string
    {
        $label = Str::title(str_replace('_', ' ', $column['name']));

        return match ($column['type']) {
            'checkbox', 'toggle' => <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1">
                        @if(\$this->{$this->modelLowerName}->{$column['name']})
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('Yes') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('No') }}</span>
                        @endif
                    </dd>
                </div>
BLADE,
            'editor' => <<<BLADE
<div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white prose dark:prose-invert max-w-none">{!! \$this->{$this->modelLowerName}->{$column['name']} !!}</dd>
                </div>
BLADE,
            'textarea' => <<<BLADE
<div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ \$this->{$this->modelLowerName}->{$column['name']} }}</dd>
                </div>
BLADE,
            'file' => <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-2">
                        @if(\$this->{$this->modelLowerName}->{$column['name']})
                            @php
                                \$fileUrl = asset('storage/' . \$this->{$this->modelLowerName}->{$column['name']});
                                \$isImage = preg_match('/\\.(jpg|jpeg|png|gif|webp|svg)\$/i', \$this->{$this->modelLowerName}->{$column['name']});
                            @endphp
                            @if(\$isImage)
                                <a href="{{ \$fileUrl }}" target="_blank" class="block">
                                    <img src="{{ \$fileUrl }}" alt="{$label}" class="max-h-48 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-primary-500 transition-all">
                                </a>
                            @else
                                <a href="{{ \$fileUrl }}" target="_blank" download class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                    <iconify-icon icon="lucide:download" class="text-base"></iconify-icon>
                                    <span>{{ basename(\$this->{$this->modelLowerName}->{$column['name']}) }}</span>
                                </a>
                            @endif
                        @else
                            <span class="text-gray-400">{{ __('No file') }}</span>
                        @endif
                    </dd>
                </div>
BLADE,
            'media' => $this->generateMediaDisplayFieldBlade($column, $label),
            default => <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ \$this->{$this->modelLowerName}->{$column['name']} }}</dd>
                </div>
BLADE,
        };
    }

    protected function generateDefaultFormField(): string
    {
        return <<<'BLADE'
<x-inputs.input
                    wire:model="name"
                    name="name"
                    label="{{ __('Name') }}"
                    placeholder="{{ __('Enter name') }}"
                    :required="true"
                />
BLADE;
    }

    protected function generateFormFieldForColumn(array $column): string
    {
        $label = Str::title(str_replace('_', ' ', $column['name']));

        // Determine if field should be required (same logic as validation)
        $isRequired = \in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || \in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger']);

        // File fields are typically not required
        if ($column['type'] === 'file') {
            $isRequired = false;
        }

        $required = $isRequired ? ':required="true"' : '';

        return match ($column['type']) {
            'textarea' => <<<BLADE
<div>
                    <label for="{$column['name']}" class="form-label">{{ __('{$label}') }}</label>
                    <textarea wire:model="{$column['name']}" id="{$column['name']}" name="{$column['name']}" rows="3"
                              placeholder="{{ __('Enter {$label}...') }}"
                              class="form-control-textarea"></textarea>
                    @error('{$column['name']}')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                    @enderror
                </div>
BLADE,
            'editor' => <<<BLADE
<div>
                    <label for="{$column['name']}" class="form-label">{{ __('{$label}') }}</label>
                    <textarea wire:model="{$column['name']}" id="{$column['name']}" name="{$column['name']}" rows="8" class="form-control-textarea"></textarea>
                    @error('{$column['name']}')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                    @enderror
                </div>
BLADE,
            'checkbox' => <<<BLADE
<label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="{$column['name']}" class="form-checkbox">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('{$label}') }}</span>
                    </div>
                </label>
BLADE,
            'toggle' => <<<BLADE
<x-inputs.toggle
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                />
BLADE,
            'select' => $this->generateSelectField($column, $label),
            'file' => $this->generateFileFieldBlade($column, $label),
            'number' => <<<BLADE
<x-inputs.input
                    type="number"
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    min="0"
                    {$required}
                />
BLADE,
            'date' => <<<BLADE
<x-inputs.input
                    type="date"
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    {$required}
                />
BLADE,
            'datetime' => <<<BLADE
<x-inputs.input
                    type="datetime-local"
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    {$required}
                />
BLADE,
            'media' => $this->generateMediaFieldBlade($column, $label),
            default => <<<BLADE
<x-inputs.input
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    placeholder="{{ __('Enter {$label}') }}"
                    {$required}
                />
BLADE,
        };
    }

    protected function generateFileFieldBlade(array $column, string $label): string
    {
        $columnName = $column['name'];
        $deleteMethod = 'delete'.Str::studly($columnName);

        return <<<BLADE
<div class="space-y-2">
                    @if(isset(\$this->{$this->modelLowerName}) && \$this->{$this->modelLowerName}->{$columnName})
                        <div class="relative inline-block">
                            @php
                                \$existingUrl = asset('storage/' . \$this->{$this->modelLowerName}->{$columnName});
                                \$isImage = preg_match('/\\.(jpg|jpeg|png|gif|webp|svg)\$/i', \$this->{$this->modelLowerName}->{$columnName});
                            @endphp
                            @if(\$isImage)
                                <a href="{{ \$existingUrl }}" target="_blank">
                                    <img src="{{ \$existingUrl }}" alt="Current {$label}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                </a>
                            @else
                                <a href="{{ \$existingUrl }}" target="_blank" download class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 pr-8">
                                    <iconify-icon icon="lucide:file" class="text-lg"></iconify-icon>
                                    <span>{{ basename(\$this->{$this->modelLowerName}->{$columnName}) }}</span>
                                </a>
                            @endif
                            <button
                                type="button"
                                wire:click="{$deleteMethod}"
                                wire:confirm="{{ __('Are you sure you want to delete this file?') }}"
                                class="absolute -top-2 -right-2 p-1 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-sm transition-colors"
                                title="{{ __('Delete file') }}"
                            >
                                <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                            </button>
                        </div>
                    @endif
                    <x-inputs.file-input
                        wire:model="{$columnName}"
                        name="{$columnName}"
                        label="{{ __('{$label}') }}"
                        hint="{{ isset(\$this->{$this->modelLowerName}) && \$this->{$this->modelLowerName}->{$columnName} ? __('Upload a new file to replace the existing one') : '' }}"
                    />
                </div>
BLADE;
    }

    protected function generateMediaDisplayFieldBlade(array $column, string $label): string
    {
        $columnName = $this->getMediaColumnName($column['name']);
        $relationName = Str::camel($column['name']);

        return <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-2">
                        @if(\$this->{$this->modelLowerName}->{$columnName} && \$this->{$this->modelLowerName}->{$relationName})
                            <a href="{{ \$this->{$this->modelLowerName}->{$relationName}Url }}" target="_blank" class="block">
                                <img src="{{ \$this->{$this->modelLowerName}->{$relationName}Url }}" alt="{$label}" class="max-h-48 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-primary-500 transition-all">
                            </a>
                        @else
                            <span class="text-gray-400">{{ __('No image') }}</span>
                        @endif
                    </dd>
                </div>
BLADE;
    }

    protected function generateMediaFieldBlade(array $column, string $label): string
    {
        $columnName = $this->getMediaColumnName($column['name']);
        $relationName = Str::camel($column['name']);

        return <<<BLADE
<div>
                    <label class="form-label">{{ __('{$label}') }}</label>
                    <div class="inline-block" x-data="{
                        selectedMediaId: @entangle('{$columnName}').live,
                        handleSelection(files) {
                            this.selectedMediaId = (files && files.length > 0) ? files[0].id : null;
                        }
                    }" x-init="window.handleMediaSelection_mediaSelector_{$columnName} = (files) => handleSelection(files)">
                        <x-media-selector
                            name="{$columnName}"
                            label=""
                            :multiple="false"
                            allowedTypes="images"
                            :existingMedia="isset(\$this->{$this->modelLowerName}) && \$this->{$this->modelLowerName}->{$columnName}
                                ? [['id' => \$this->{$this->modelLowerName}->{$columnName}, 'url' => \$this->{$this->modelLowerName}->{$relationName}Url, 'name' => \$this->{$this->modelLowerName}->{$relationName}?->name]]
                                : []"
                            :showPreview="true"
                            class="max-w-xs"
                        />
                    </div>
                    @error('{$columnName}')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                    @enderror
                </div>
BLADE;
    }

    protected function generateSelectField(array $column, string $label): string
    {
        // If options are defined, create static select
        if (! empty($column['options'])) {
            $optionsPhp = "[\n";
            foreach ($column['options'] as $option) {
                $key = Str::slug($option, '_');
                $optionsPhp .= "                        '{$key}' => __('{$option}'),\n";
            }
            $optionsPhp .= '                    ]';

            return <<<BLADE
<x-inputs.select
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    placeholder="{{ __('Select {$label}') }}"
                    :options="{$optionsPhp}"
                />
BLADE;
        }

        // Default select with placeholder for manual options
        return <<<BLADE
<x-inputs.select
                    wire:model="{$column['name']}"
                    name="{$column['name']}"
                    label="{{ __('{$label}') }}"
                    placeholder="{{ __('Select {$label}') }}"
                    :options="[]"
                />
                {{-- TODO: Add options array to the component --}}
BLADE;
    }

    protected function hasEditorFields(): bool
    {
        foreach ($this->columns as $column) {
            if ($column['type'] === 'editor') {
                return true;
            }
        }

        return false;
    }

    protected function getEditorFields(): array
    {
        return array_filter($this->columns, fn ($column) => $column['type'] === 'editor');
    }

    protected function generateEditorAssets(): string
    {
        if (! $this->hasEditorFields()) {
            return '';
        }

        $editorFields = $this->getEditorFields();
        $editorIds = array_values(array_map(fn ($col) => $col['name'], $editorFields));
        $editorIdsJson = json_encode($editorIds);

        $content = $this->getStub('editor-assets');

        return str_replace('$EDITOR_IDS$', $editorIdsJson, $content);
    }

    protected function listAvailableModules(): void
    {
        $modulesPath = base_path('modules');
        $this->line('Available modules:');

        if (is_dir($modulesPath)) {
            $dirs = scandir($modulesPath);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || ! is_dir("{$modulesPath}/{$dir}")) {
                    continue;
                }
                if (str_starts_with($dir, '.') || str_starts_with($dir, '_')) {
                    continue;
                }
                $this->line("  - {$dir}");
            }
        }
    }

    protected function showGeneratedFiles(): void
    {
        if ($this->migrationCreated) {
            $this->line("  - modules/{$this->moduleStudlyName}/database/migrations/*_create_{$this->tableName}_table.php");
        }
        $this->line("  - modules/{$this->moduleStudlyName}/app/Models/{$this->modelStudlyName}.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Livewire/Components/{$this->modelStudlyName}Datatable.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Index.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Show.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Create.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Edit.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/layouts/crud.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/index.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/show.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/create.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/edit.blade.php");
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
