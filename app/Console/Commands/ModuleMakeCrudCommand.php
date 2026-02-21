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
                            {--fields= : Field definitions (e.g., "title:string,content:text,featured_image:media,status:select:Active|Inactive,is_active:toggle")}
                            {--columns=2 : Number of form columns (1 or 2, default: 2)}';

    protected $description = 'Generate CRUD components (Model, Controller, Service, FormRequest, Datatable, Views) for a module';

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

    protected string $modelSnake;

    protected string $tableName;

    protected array $columns = [];

    protected int $formColumns = 2;

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
            '$MODEL_SNAKE$' => $this->modelSnake,
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
        $possiblePaths = [
            base_path("modules/{$this->moduleStudlyName}"),
            base_path('modules/'.Str::kebab($this->moduleName)),
            base_path("modules/{$this->moduleLowerName}"),
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

        $modulePath = $this->resolveModulePath();
        if (! $modulePath) {
            $this->error("Module '{$this->moduleStudlyName}' not found in modules/ directory.");
            $this->listAvailableModules();

            return self::FAILURE;
        }

        $migrationOption = $this->option('migration');
        $modelOption = $this->option('model');

        if (! $migrationOption && ! $modelOption) {
            $this->error('You must provide either --migration or --model option.');

            return self::FAILURE;
        }

        if ($migrationOption) {
            $this->parseMigration($migrationOption);
        } else {
            $this->modelName = $modelOption;
            if (str_contains($this->modelName, '/') || str_contains($this->modelName, '\\')) {
                $this->modelName = pathinfo($this->modelName, PATHINFO_FILENAME);
                $this->modelName = preg_replace('/\.php$/i', '', $this->modelName);
            }
            $this->modelStudlyName = Str::studly($this->modelName);
            $this->modelLowerName = Str::lower($this->modelName);
            $this->modelPluralName = Str::plural($this->modelStudlyName);
            $this->modelPluralLower = Str::lower($this->modelPluralName);
            $this->modelSnake = Str::snake($this->modelStudlyName);
            $this->tableName = $this->moduleLowerName.'_'.Str::snake($this->modelPluralName);

            $this->tryAutoDetectMigration();
        }

        $columnsOption = (int) ($this->option('columns') ?? 2);
        $this->formColumns = in_array($columnsOption, [1, 2]) ? $columnsOption : 2;

        $this->info("Generating CRUD for {$this->modelStudlyName} in {$this->moduleStudlyName} module...");
        $this->newLine();

        // Generate files
        $this->generateModel();
        $this->generateDatatable();
        $this->generateModuleController();
        $this->generateController();
        $this->generateService();
        $this->generateFormRequest();
        $this->generateViews();
        $this->generatePermissionMigration();
        $this->generatePolicy();
        $this->registerPolicyInServiceProvider();
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
        $this->line('  4. Customize the form partial with appropriate input fields');
        $this->line('  5. Clear route cache: php artisan optimize:clear');

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
        $migrationsPath = "{$this->modulePath}/database/migrations";
        $files = glob("{$migrationsPath}/*{$migrationName}*.php");

        if (empty($files)) {
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

        if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            $this->tableName = $matches[1];
        } else {
            $this->error('Could not find table name in migration file.');
            exit(self::FAILURE);
        }

        $tableWithoutPrefix = preg_replace("/^{$this->moduleLowerName}_/", '', $this->tableName);
        $this->modelStudlyName = Str::studly(Str::singular($tableWithoutPrefix));
        $this->modelName = $this->modelStudlyName;
        $this->modelLowerName = Str::lower($this->modelStudlyName);
        $this->modelPluralName = Str::plural($this->modelStudlyName);
        $this->modelPluralLower = Str::lower($this->modelPluralName);
        $this->modelSnake = Str::snake($this->modelStudlyName);

        $this->parseColumns($content);
    }

    protected function tryAutoDetectMigration(): void
    {
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

        if ($fieldsOption) {
            $this->parseFieldsOption($fieldsOption);

            if ($migrationFile) {
                $this->info('Auto-detected migration: '.basename($migrationFile));
                $content = file_get_contents($migrationFile);

                if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                    $this->tableName = $matches[1];
                }
            } else {
                $this->generateMigration();
            }
        } elseif ($migrationFile) {
            $this->info('Auto-detected migration: '.basename($migrationFile));
            $content = file_get_contents($migrationFile);

            if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                $this->tableName = $matches[1];
            }

            $this->parseColumns($content);
        } else {
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

            if (\in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $column = [
                'name' => $name,
                'type' => $this->mapColumnType($type),
                'dbType' => $this->mapDbType($type),
            ];

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

            $isNullable = in_array($column['dbType'], ['text', 'json', 'date', 'datetime']) || $column['type'] === 'file';
            $nullable = $isNullable ? '->nullable()' : '';
            $default = $column['dbType'] === 'boolean' ? '->default(false)' : '';

            $lines[] = "            \$table->{$method}('{$column['name']}'){$nullable}{$default};";
        }

        return implode("\n", $lines);
    }

    /**
     * Get the database column name for a media field.
     */
    protected function getMediaColumnName(string $fieldName): string
    {
        return str_ends_with($fieldName, '_id') ? $fieldName : $fieldName.'_id';
    }

    protected function parseColumns(string $content): void
    {
        $this->columns = [];

        $pattern = '/\$table->(\w+)\s*\(\s*[\'"](\w+)[\'"]/';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $type = $match[1];
                $name = $match[2];

                if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }

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

        if (File::exists($path)) {
            $this->line("  Skipped: Models/{$this->modelStudlyName}.php (already exists)");

            return;
        }

        $fillable = $this->generateFillableArray();
        $casts = $this->generateCastsArray();
        $hasMedia = $this->hasMediaFields();
        $mediaImports = $hasMedia ? "use App\\Models\\Media;\nuse Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;\n" : '';
        $mediaRelationships = $this->generateMediaRelationships();

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
     * Check if any fields are file type.
     */
    protected function hasFileFields(): bool
    {
        foreach ($this->columns as $column) {
            if ($column['type'] === 'file') {
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

        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Components/{$this->modelStudlyName}Datatable.php (already exists)");

            return;
        }

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

    /**
     * Generate the base module controller (if it doesn't exist).
     */
    protected function generateModuleController(): void
    {
        $path = "{$this->modulePath}/app/Http/Controllers/{$this->moduleStudlyName}Controller.php";

        if (File::exists($path)) {
            return;
        }

        $content = $this->getStub('module-controller');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Http/Controllers/{$this->moduleStudlyName}Controller.php");
    }

    /**
     * Generate the CRUD controller.
     */
    protected function generateController(): void
    {
        $path = "{$this->modulePath}/app/Http/Controllers/{$this->modelStudlyName}Controller.php";

        if (File::exists($path)) {
            $this->line("  Skipped: Http/Controllers/{$this->modelStudlyName}Controller.php (already exists)");

            return;
        }

        $content = $this->getStub('controller');
        $content = $this->replaceStubTokens($content, [
            '$FILE_UPLOAD_STORE$' => $this->generateFileUploadStoreCode(),
            '$FILE_UPLOAD_UPDATE$' => $this->generateFileUploadUpdateCode(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Http/Controllers/{$this->modelStudlyName}Controller.php");
    }

    /**
     * Generate the service class.
     */
    protected function generateService(): void
    {
        $path = "{$this->modulePath}/app/Services/{$this->modelStudlyName}Service.php";

        if (File::exists($path)) {
            $this->line("  Skipped: Services/{$this->modelStudlyName}Service.php (already exists)");

            return;
        }

        $content = $this->getStub('service');
        $content = $this->replaceStubTokens($content, [
            '$SEARCH_CONDITIONS$' => $this->generateServiceSearchConditions(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Services/{$this->modelStudlyName}Service.php");
    }

    /**
     * Generate the FormRequest class.
     */
    protected function generateFormRequest(): void
    {
        $path = "{$this->modulePath}/app/Http/Requests/{$this->modelStudlyName}Request.php";

        if (File::exists($path)) {
            $this->line("  Skipped: Http/Requests/{$this->modelStudlyName}Request.php (already exists)");

            return;
        }

        $content = $this->getStub('form-request');
        $content = $this->replaceStubTokens($content, [
            '$RULES$' => $this->generateValidationRules(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Http/Requests/{$this->modelStudlyName}Request.php");
    }

    protected function generateViews(): void
    {
        $this->generateIndexView();
        $this->generateCreateView();
        $this->generateEditView();
        $this->generateShowView();
        $this->generateFormPartial();
    }

    protected function generateIndexView(): void
    {
        $path = "{$this->modulePath}/resources/views/pages/{$this->modelPluralLower}/index.blade.php";

        if (File::exists($path)) {
            $this->line("  Skipped: views/pages/{$this->modelPluralLower}/index.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/index');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/pages/{$this->modelPluralLower}/index.blade.php");
    }

    protected function generateCreateView(): void
    {
        $path = "{$this->modulePath}/resources/views/pages/{$this->modelPluralLower}/create.blade.php";

        if (File::exists($path)) {
            $this->line("  Skipped: views/pages/{$this->modelPluralLower}/create.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/create');
        $content = $this->replaceStubTokens($content, [
            '$EDITOR_ASSETS$' => $this->generateBladeEditorAssets(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/pages/{$this->modelPluralLower}/create.blade.php");
    }

    protected function generateEditView(): void
    {
        $path = "{$this->modulePath}/resources/views/pages/{$this->modelPluralLower}/edit.blade.php";

        if (File::exists($path)) {
            $this->line("  Skipped: views/pages/{$this->modelPluralLower}/edit.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/edit');
        $content = $this->replaceStubTokens($content, [
            '$EDITOR_ASSETS$' => $this->generateBladeEditorAssets(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/pages/{$this->modelPluralLower}/edit.blade.php");
    }

    protected function generateShowView(): void
    {
        $path = "{$this->modulePath}/resources/views/pages/{$this->modelPluralLower}/show.blade.php";

        if (File::exists($path)) {
            $this->line("  Skipped: views/pages/{$this->modelPluralLower}/show.blade.php (already exists)");

            return;
        }

        $content = $this->getStub('views/show');
        $content = $this->replaceStubTokens($content, [
            '$DISPLAY_FIELDS$' => $this->generateDisplayFields(),
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/pages/{$this->modelPluralLower}/show.blade.php");
    }

    protected function generateFormPartial(): void
    {
        $path = "{$this->modulePath}/resources/views/pages/{$this->modelPluralLower}/partials/form.blade.php";

        if (File::exists($path)) {
            $this->line("  Skipped: views/pages/{$this->modelPluralLower}/partials/form.blade.php (already exists)");

            return;
        }

        $containerClass = $this->formColumns === 2
            ? 'grid grid-cols-1 sm:grid-cols-2 gap-5'
            : 'space-y-5';
        $submitColSpan = $this->formColumns === 2 ? ' sm:col-span-2' : '';

        $content = $this->getStub('views/form');
        $content = $this->replaceStubTokens($content, [
            '$FORM_FIELDS$' => $this->generateFormFields(),
            '$FORM_CONTAINER_CLASS$' => $containerClass,
            '$SUBMIT_COL_SPAN$' => $submitColSpan,
        ]);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/pages/{$this->modelPluralLower}/partials/form.blade.php");
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
            $this->line('  Routes already exist, skipping...');

            return;
        }

        // Add controller use statement
        $useStatement = "use Modules\\{$this->moduleStudlyName}\\Http\\Controllers\\{$this->modelStudlyName}Controller;";

        // Find the last use statement and add after it
        if (preg_match_all('/^use [^;]+;$/m', $content, $matches)) {
            $lastUseStatement = end($matches[0]);
            $lastPos = strrpos($content, $lastUseStatement);
            $insertPos = $lastPos + strlen($lastUseStatement);
            $content = substr_replace($content, "\n".$useStatement, $insertPos, 0);
        }

        // Add resource route before closing of the group
        $newRoute = "\n\n        Route::resource('{$this->modelPluralLower}', {$this->modelStudlyName}Controller::class);";

        // Find the first }); that closes the route group and insert before it
        $content = preg_replace('/(\n\s*}\s*\)\s*;)/', $newRoute.'$1', $content, 1);

        File::put($routesPath, $content);
        $this->info('  Updated: routes/web.php');
    }

    protected function updateMenuService(): void
    {
        $menuServicePath = "{$this->modulePath}/app/Services/MenuService.php";

        if (! File::exists($menuServicePath)) {
            $menuServicePath = "{$this->modulePath}/app/Services/{$this->moduleStudlyName}MenuService.php";
        }

        if (! File::exists($menuServicePath)) {
            $this->line('  MenuService not found, skipping menu update...');
            $this->line("  You can manually add a menu item for {$this->modelPluralName}");

            return;
        }

        $content = File::get($menuServicePath);

        if (str_contains($content, "'{$this->moduleLowerName}-{$this->modelPluralLower}'") ||
            str_contains($content, "admin.{$this->moduleLowerName}.{$this->modelPluralLower}.index")) {
            $this->line('  Menu item already exists, skipping...');

            return;
        }

        $submenuItem = <<<PHP
(new AdminMenuItem())->setAttributes([
                'label' => __('{$this->modelPluralName}'),
                'icon' => '',
                'route' => route('admin.{$this->moduleLowerName}.{$this->modelPluralLower}.index'),
                'active' => Route::is('admin.{$this->moduleLowerName}.{$this->modelPluralLower}.*'),
                'id' => '{$this->moduleLowerName}-{$this->modelPluralLower}',
                'permissions' => [],
            ])
PHP;

        $updated = false;

        if (preg_match('/return\s+\$menu\s*;/', $content)) {
            $submenuCode = <<<PHP

        // {$this->modelPluralName} submenu
        \$menu->setChildren(array_merge(\$menu->children, [
            {$submenuItem},
        ]));
PHP;
            $content = preg_replace(
                '/(return\s+\$menu\s*;)/',
                $submenuCode."\n\n        $1",
                $content,
                1
            );
            $updated = true;
        } elseif (preg_match('/return\s+\(new\s+AdminMenuItem\(\)\)->setAttributes\s*\(\s*\[/', $content)) {
            $pattern = '/(public\s+function\s+getMenu\s*\(\s*\)\s*:\s*AdminMenuItem\s*\{\s*)(return\s+\(new\s+AdminMenuItem\(\)\)->setAttributes\s*\(\s*\[)(.*?)(\]\s*\)\s*;)(\s*\})/s';

            if (preg_match($pattern, $content, $matches)) {
                $methodStart = $matches[1];
                $attributesContent = $matches[3];
                $methodEnd = $matches[5];

                $newMethod = <<<PHP
{$methodStart}\$menu = (new AdminMenuItem())->setAttributes([{$attributesContent}]);

        // {$this->modelPluralName} submenu
        \$menu->setChildren([
            {$submenuItem},
        ]);

        return \$menu;{$methodEnd}
PHP;

                $content = preg_replace($pattern, $newMethod, $content, 1);
                $updated = true;
            }
        }

        if ($updated) {
            File::put($menuServicePath, $content);
            $this->info('  Updated: MenuService with submenu item');
        } else {
            $this->line('  Could not update MenuService automatically');
            $this->line("  Please add menu item for {$this->modelPluralName} manually");
        }
    }

    protected function generateFillableArray(): string
    {
        if (empty($this->columns)) {
            return "'name',";
        }

        $fillable = [];
        foreach ($this->columns as $column) {
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
            foreach ($this->columns as $column) {
                $label = Str::title(str_replace('_', ' ', $column['name']));
                $isSearchable = in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText']);
                $searchableStr = $isSearchable ? "\n                'searchable' => true," : '';
                $widthStr = '';

                if (in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'tinyInteger', 'smallInteger', 'boolean', 'date'])) {
                    $widthStr = "\n                'width' => '120px',";
                }

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

        $headers[] = <<<'PHP'
[
                'id' => 'created_at',
                'title' => __('Created'),
                'sortable' => true,
                'sortBy' => 'created_at',
                'width' => '150px',
            ]
PHP;

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
                if (in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])) {
                    $searchableColumns[] = $column['name'];
                }
            }
        }

        if (empty($searchableColumns)) {
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

    /**
     * Generate search conditions for the service class.
     */
    protected function generateServiceSearchConditions(): string
    {
        $searchableColumns = [];

        if (empty($this->columns)) {
            $searchableColumns[] = 'name';
        } else {
            foreach ($this->columns as $column) {
                if (in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])) {
                    $searchableColumns[] = $column['name'];
                }
            }
        }

        if (empty($searchableColumns)) {
            $searchableColumns[] = $this->columns[0]['name'] ?? 'id';
        }

        $conditions = [];
        foreach ($searchableColumns as $index => $columnName) {
            if ($index === 0) {
                $conditions[] = "\$q->where('{$columnName}', 'like', \"%{\$search}%\")";
            } else {
                $conditions[] = "                        ->orWhere('{$columnName}', 'like', \"%{\$search}%\")";
            }
        }

        return implode("\n", $conditions).';';
    }

    /**
     * Generate file upload handling code for the store method.
     */
    protected function generateFileUploadStoreCode(): string
    {
        $lines = [];

        foreach ($this->columns as $column) {
            if ($column['type'] !== 'file') {
                continue;
            }

            $fieldName = $column['name'];
            $storePath = $this->modelPluralLower;
            $lines[] = "        if (\$request->hasFile('{$fieldName}')) {";
            $lines[] = "            \$validated['{$fieldName}'] = \$request->file('{$fieldName}')->store('{$storePath}', 'public');";
            $lines[] = "        }";
        }

        return empty($lines) ? '' : "\n".implode("\n", $lines);
    }

    /**
     * Generate file upload handling code for the update method.
     */
    protected function generateFileUploadUpdateCode(): string
    {
        $lines = [];

        foreach ($this->columns as $column) {
            if ($column['type'] !== 'file') {
                continue;
            }

            $fieldName = $column['name'];
            $storePath = $this->modelPluralLower;
            $modelVar = '$'.$this->modelLowerName;
            $lines[] = "        if (\$request->hasFile('{$fieldName}')) {";
            $lines[] = "            \$validated['{$fieldName}'] = \$request->file('{$fieldName}')->store('{$storePath}', 'public');";
            $lines[] = "        } else {";
            $lines[] = "            unset(\$validated['{$fieldName}']); // Keep existing value";
            $lines[] = "        }";
        }

        return empty($lines) ? '' : "\n".implode("\n", $lines);
    }

    protected function generateValidationRules(): string
    {
        if (empty($this->columns)) {
            return "'name' => 'required|string|max:255',";
        }

        $rules = [];
        foreach ($this->columns as $column) {
            $rule = $this->getValidationRule($column);
            if ($column['type'] === 'file') {
                $rules[] = "'{$column['name']}' => ['nullable', 'file', 'max:10240']";
            } elseif ($column['type'] === 'media') {
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

        $isRequired = \in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || \in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger']);

        if ($column['type'] === 'file') {
            $isRequired = false;
        }

        $rules[] = $isRequired ? 'required' : 'nullable';

        match ($column['type']) {
            'text' => $rules[] = 'string|max:255',
            'textarea' => $rules[] = 'string|max:1000',
            'editor' => $rules[] = 'string|max:65535',
            'number' => $rules[] = 'integer|min:0',
            'checkbox', 'toggle' => $rules[] = 'boolean',
            'date' => $rules[] = 'date',
            'datetime' => $rules[] = 'date',
            'select' => $rules[] = 'string|max:255',
            'file' => $rules[] = 'file|max:10240',
            default => $rules[] = 'string',
        };

        return implode('|', $rules);
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

        return implode("\n\n    ", $fields);
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
        $modelVar = '$'.$this->modelLowerName;

        return <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ {$modelVar}->name }}</dd>
                </div>
BLADE;
    }

    protected function generateDisplayFieldForColumn(array $column): string
    {
        $label = Str::title(str_replace('_', ' ', $column['name']));
        $modelVar = '$'.$this->modelLowerName;

        return match ($column['type']) {
            'checkbox', 'toggle' => <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1">
                        @if({$modelVar}->{$column['name']})
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
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white prose dark:prose-invert max-w-none">{!! {$modelVar}->{$column['name']} !!}</dd>
                </div>
BLADE,
            'textarea' => <<<BLADE
<div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ {$modelVar}->{$column['name']} }}</dd>
                </div>
BLADE,
            'file' => <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-2">
                        @if({$modelVar}->{$column['name']})
                            @php
                                \$fileUrl = asset('storage/' . {$modelVar}->{$column['name']});
                                \$isImage = preg_match('/\\.(jpg|jpeg|png|gif|webp|svg)\$/i', {$modelVar}->{$column['name']});
                            @endphp
                            @if(\$isImage)
                                <a href="{{ \$fileUrl }}" target="_blank" class="block">
                                    <img src="{{ \$fileUrl }}" alt="{$label}" class="max-h-48 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-primary-500 transition-all">
                                </a>
                            @else
                                <a href="{{ \$fileUrl }}" target="_blank" download class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                    <iconify-icon icon="lucide:download" class="text-base"></iconify-icon>
                                    <span>{{ basename({$modelVar}->{$column['name']}) }}</span>
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
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ {$modelVar}->{$column['name']} }}</dd>
                </div>
BLADE,
        };
    }

    protected function generateDefaultFormField(): string
    {
        return <<<'BLADE'
<x-inputs.input
        name="name"
        label="{{ __('Name') }}"
        placeholder="{{ __('Enter name') }}"
        :value="isset($MODEL_LOWER$) ? $MODEL_LOWER$->name : ''"
        :required="true"
    />
BLADE;
    }

    protected function generateFormFieldForColumn(array $column): string
    {
        $label = Str::title(str_replace('_', ' ', $column['name']));
        $modelVar = '$'.$this->modelLowerName;

        $isRequired = \in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || \in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger']);

        if ($column['type'] === 'file') {
            $isRequired = false;
        }

        $required = $isRequired ? ':required="true"' : '';
        $valueExpr = "isset({$modelVar}) ? {$modelVar}->{$column['name']} : ''";
        $nullableValueExpr = "isset({$modelVar}) ? {$modelVar}->{$column['name']} : null";
        $fullWidthAttr = $this->formColumns === 2 ? ' class="sm:col-span-2"' : '';

        return match ($column['type']) {
            'textarea' => <<<BLADE
<div{$fullWidthAttr}>
        <label for="{$column['name']}" class="form-label">{{ __('{$label}') }}</label>
        <textarea name="{$column['name']}" id="{$column['name']}" rows="3"
                  placeholder="{{ __('Enter {$label}...') }}"
                  class="form-control-textarea">{{ old('{$column['name']}', {$valueExpr}) }}</textarea>
        @error('{$column['name']}')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
        @enderror
    </div>
BLADE,
            'editor' => <<<BLADE
<div{$fullWidthAttr}>
        <label for="{$column['name']}" class="form-label">{{ __('{$label}') }}</label>
        <textarea name="{$column['name']}" id="{$column['name']}" rows="8"
                  class="form-control-textarea">{{ old('{$column['name']}', {$valueExpr}) }}</textarea>
        @error('{$column['name']}')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
        @enderror
    </div>
BLADE,
            'checkbox' => <<<BLADE
<label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="{$column['name']}" value="1" class="form-checkbox"
               @if(old('{$column['name']}', {$nullableValueExpr})) checked @endif>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('{$label}') }}</span>
    </label>
BLADE,
            'toggle' => <<<BLADE
<x-inputs.toggle
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        :checked="old('{$column['name']}', {$nullableValueExpr}) ? true : false"
    />
BLADE,
            'select' => $this->generateSelectField($column, $label, $modelVar),
            'file' => $this->generateFileFieldBlade($column, $label, $modelVar, $fullWidthAttr),
            'number' => <<<BLADE
<x-inputs.input
        type="number"
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        :value="{$valueExpr}"
        min="0"
        {$required}
    />
BLADE,
            'date' => <<<BLADE
<x-inputs.input
        type="date"
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        :value="{$valueExpr}"
        {$required}
    />
BLADE,
            'datetime' => <<<BLADE
<x-inputs.input
        type="datetime-local"
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        :value="isset({$modelVar}) ? ({$modelVar}->{$column['name']}?->format('Y-m-d\TH:i') ?? '') : ''"
        {$required}
    />
BLADE,
            'media' => $this->generateMediaFieldBlade($column, $label, $modelVar),
            default => <<<BLADE
<x-inputs.input
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        placeholder="{{ __('Enter {$label}') }}"
        :value="{$valueExpr}"
        {$required}
    />
BLADE,
        };
    }

    protected function generateFileFieldBlade(array $column, string $label, string $modelVar, string $fullWidthAttr = ''): string
    {
        $columnName = $column['name'];
        $fileClass = $fullWidthAttr ? 'space-y-2 sm:col-span-2' : 'space-y-2';

        return <<<BLADE
<div class="{$fileClass}">
        @if(isset({$modelVar}) && {$modelVar}->{$columnName})
            <div class="relative inline-block">
                @php
                    \$existingUrl = asset('storage/' . {$modelVar}->{$columnName});
                    \$isImage = preg_match('/\\.(jpg|jpeg|png|gif|webp|svg)\$/i', {$modelVar}->{$columnName});
                @endphp
                @if(\$isImage)
                    <a href="{{ \$existingUrl }}" target="_blank">
                        <img src="{{ \$existingUrl }}" alt="Current {$label}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                    </a>
                @else
                    <a href="{{ \$existingUrl }}" target="_blank" download class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        <iconify-icon icon="lucide:file" class="text-lg"></iconify-icon>
                        <span>{{ basename({$modelVar}->{$columnName}) }}</span>
                    </a>
                @endif
            </div>
        @endif
        <x-inputs.file-input
            name="{$columnName}"
            label="{{ __('{$label}') }}"
            hint="{{ isset({$modelVar}) && {$modelVar}->{$columnName} ? __('Upload a new file to replace the existing one') : '' }}"
        />
    </div>
BLADE;
    }

    protected function generateMediaDisplayFieldBlade(array $column, string $label): string
    {
        $columnName = $this->getMediaColumnName($column['name']);
        $relationName = Str::camel($column['name']);
        $modelVar = '$'.$this->modelLowerName;

        return <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-2">
                        @if({$modelVar}->{$columnName} && {$modelVar}->{$relationName})
                            <a href="{{ {$modelVar}->{$relationName}Url }}" target="_blank" class="block">
                                <img src="{{ {$modelVar}->{$relationName}Url }}" alt="{$label}" class="max-h-48 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-primary-500 transition-all">
                            </a>
                        @else
                            <span class="text-gray-400">{{ __('No image') }}</span>
                        @endif
                    </dd>
                </div>
BLADE;
    }

    protected function generateMediaFieldBlade(array $column, string $label, string $modelVar, string $fullWidthAttr = ''): string
    {
        $columnName = $this->getMediaColumnName($column['name']);
        $relationName = Str::camel($column['name']);

        return <<<BLADE
<div{$fullWidthAttr}>
        <label class="form-label">{{ __('{$label}') }}</label>
        <x-media-selector
            name="{$columnName}"
            label=""
            :multiple="false"
            allowedTypes="images"
            :existingMedia="isset({$modelVar}) && {$modelVar}->{$columnName} && {$modelVar}->{$relationName}
                ? [['id' => {$modelVar}->{$columnName}, 'url' => {$modelVar}->{$relationName}Url, 'name' => {$modelVar}->{$relationName}?->name]]
                : []"
            :showPreview="true"
            class="max-w-xs"
        />
        @error('{$columnName}')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
        @enderror
    </div>
BLADE;
    }

    protected function generateSelectField(array $column, string $label, string $modelVar): string
    {
        if (! empty($column['options'])) {
            $optionsPhp = "[\n";
            foreach ($column['options'] as $option) {
                $key = Str::slug($option, '_');
                $optionsPhp .= "                    '{$key}' => __('{$option}'),\n";
            }
            $optionsPhp .= "                ]";

            return <<<BLADE
<x-inputs.select
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        placeholder="{{ __('Select {$label}') }}"
        :value="isset({$modelVar}) ? {$modelVar}->{$column['name']} : ''"
        :options="{$optionsPhp}"
    />
BLADE;
        }

        return <<<BLADE
<x-inputs.select
        name="{$column['name']}"
        label="{{ __('{$label}') }}"
        placeholder="{{ __('Select {$label}') }}"
        :value="isset({$modelVar}) ? {$modelVar}->{$column['name']} : ''"
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

    /**
     * Generate editor assets for Blade views (uses @push instead of @assets/@script).
     */
    protected function generateBladeEditorAssets(): string
    {
        if (! $this->hasEditorFields()) {
            return '';
        }

        $editorFields = $this->getEditorFields();
        $editorIds = array_values(array_map(fn ($col) => $col['name'], $editorFields));
        $editorIdsJson = json_encode($editorIds);

        $content = $this->getStub('blade-editor-assets');

        return str_replace('$EDITOR_IDS$', $editorIdsJson, $content);
    }

    /**
     * Generate a permission migration for this model's CRUD permissions.
     */
    protected function generatePermissionMigration(): void
    {
        $migrationName = "add_{$this->modelSnake}_permissions";
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";

        $migrationsPath = "{$this->modulePath}/database/migrations";
        $this->ensureDirectoryExists($migrationsPath);

        // Skip if a matching migration already exists
        if (! empty(glob("{$migrationsPath}/*{$migrationName}*.php"))) {
            $this->line("  Skipped: permission migration (already exists)");

            return;
        }

        $path = "{$migrationsPath}/{$filename}";
        $content = $this->getStub('permission-migration');
        $content = $this->replaceStubTokens($content);

        File::put($path, $content);
        $this->info("  Created: database/migrations/{$filename}");
    }

    /**
     * Generate a Policy class for this model.
     */
    protected function generatePolicy(): void
    {
        $path = "{$this->modulePath}/app/Policies/{$this->modelStudlyName}Policy.php";

        if (File::exists($path)) {
            $this->line("  Skipped: Policies/{$this->modelStudlyName}Policy.php (already exists)");

            return;
        }

        $content = $this->getStub('policy');
        $content = $this->replaceStubTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Policies/{$this->modelStudlyName}Policy.php");
    }

    /**
     * Register the generated Policy in the module's ServiceProvider.
     */
    protected function registerPolicyInServiceProvider(): void
    {
        $providerPath = "{$this->modulePath}/app/Providers/{$this->moduleStudlyName}ServiceProvider.php";

        if (! File::exists($providerPath)) {
            $this->line("  ServiceProvider not found — register policy manually:");
            $this->line("    Gate::policy({$this->modelStudlyName}::class, {$this->modelStudlyName}Policy::class);");

            return;
        }

        $content = File::get($providerPath);

        // Skip if already registered
        if (str_contains($content, "{$this->modelStudlyName}Policy::class")) {
            $this->line('  Policy already registered in ServiceProvider, skipping...');

            return;
        }

        $modelFqn = "Modules\\{$this->moduleStudlyName}\\Models\\{$this->modelStudlyName}";
        $policyFqn = "Modules\\{$this->moduleStudlyName}\\Policies\\{$this->modelStudlyName}Policy";

        // Add Gate facade import if missing
        if (! str_contains($content, 'use Illuminate\Support\Facades\Gate;')) {
            $content = str_replace(
                'use Illuminate\Support\ServiceProvider;',
                "use Illuminate\Support\Facades\Gate;\nuse Illuminate\Support\ServiceProvider;",
                $content
            );
        }

        // Add model + policy imports before the class declaration
        $importBlock = "use {$modelFqn};\nuse {$policyFqn};";
        if (! str_contains($content, "use {$modelFqn};")) {
            // Insert right after the Gate import line
            $content = preg_replace(
                '/(use Illuminate\\\\Support\\\\Facades\\\\Gate;)/',
                "$1\n{$importBlock}",
                $content,
                1
            );
        }

        // Add Gate::policy() at the top of boot()
        $policyCall = "        Gate::policy({$this->modelStudlyName}::class, {$this->modelStudlyName}Policy::class);";
        $content = preg_replace(
            '/(public function boot\(\): void\s*\{)/',
            "$1\n{$policyCall}",
            $content,
            1
        );

        File::put($providerPath, $content);
        $this->info("  Updated: Providers/{$this->moduleStudlyName}ServiceProvider.php (policy registered)");
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
        $this->line("  - modules/{$this->moduleStudlyName}/app/Http/Controllers/{$this->moduleStudlyName}Controller.php (base, if new)");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Http/Controllers/{$this->modelStudlyName}Controller.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Policies/{$this->modelStudlyName}Policy.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Services/{$this->modelStudlyName}Service.php");
        $this->line("  - modules/{$this->moduleStudlyName}/app/Http/Requests/{$this->modelStudlyName}Request.php");
        $this->line("  - modules/{$this->moduleStudlyName}/database/migrations/*_add_{$this->modelSnake}_permissions.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/pages/{$this->modelPluralLower}/index.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/pages/{$this->modelPluralLower}/create.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/pages/{$this->modelPluralLower}/edit.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/pages/{$this->modelPluralLower}/show.blade.php");
        $this->line("  - modules/{$this->moduleStudlyName}/resources/views/pages/{$this->modelPluralLower}/partials/form.blade.php");
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
