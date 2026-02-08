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
                            {--fields= : Field definitions (e.g., "title:string,content:text,is_active:boolean")}';

    protected $description = 'Generate CRUD components (Model, Datatable, Create, Edit, Index) for a module';

    protected bool $migrationCreated = false;

    protected string $moduleName;

    protected string $moduleStudlyName;

    protected string $moduleLowerName;

    protected string $modelName;

    protected string $modelStudlyName;

    protected string $modelLowerName;

    protected string $modelPluralName;

    protected string $modelPluralLower;

    protected string $tableName;

    protected array $columns = [];

    public function handle(): int
    {
        $this->moduleName = $this->argument('module');
        $this->moduleStudlyName = Str::studly($this->moduleName);
        $this->moduleLowerName = Str::lower($this->moduleName);

        // Validate module exists
        $modulePath = base_path("modules/{$this->moduleStudlyName}");
        if (! is_dir($modulePath)) {
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
        $migrationsPath = base_path("modules/{$this->moduleStudlyName}/database/migrations");
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

        $migrationsPath = base_path("modules/{$this->moduleStudlyName}/database/migrations");
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

        if ($migrationFile) {
            $this->info('Auto-detected migration: '.basename($migrationFile));
            $content = file_get_contents($migrationFile);

            // Extract table name from Schema::create
            if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                $this->tableName = $matches[1];
            }

            // Parse columns from migration
            $this->parseColumns($content);
        } else {
            // No migration found - check for --fields option or prompt
            $fieldsOption = $this->option('fields');

            if ($fieldsOption) {
                // Parse fields from option and create migration
                $this->parseFieldsOption($fieldsOption);
                $this->generateMigration();
            } else {
                // Prompt for fields interactively
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

            // Skip id and timestamps
            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $this->columns[] = [
                'name' => $name,
                'type' => $this->mapColumnType($type),
                'dbType' => $type,
            ];
        }
    }

    protected function promptForFields(): void
    {
        $this->columns = [];
        $this->info('Define your fields (press Enter with empty name to finish):');
        $this->line('  Supported types: string, text, integer, boolean, date, datetime, decimal, json');
        $this->newLine();

        $fieldNumber = 1;
        while (true) {
            $name = $this->ask("Field {$fieldNumber} name (or press Enter to finish)");

            if (empty($name)) {
                break;
            }

            // Validate field name
            $name = Str::snake(trim($name));

            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $this->warn("  '{$name}' is auto-generated, skipping...");

                continue;
            }

            $type = $this->choice(
                "Field {$fieldNumber} type for '{$name}'",
                ['string', 'text', 'integer', 'boolean', 'date', 'datetime', 'decimal', 'json'],
                0
            );

            $this->columns[] = [
                'name' => $name,
                'type' => $this->mapColumnType($type),
                'dbType' => $type,
            ];

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
        $snakePlural = Str::snake($this->modelPluralName);
        $migrationName = "create_{$this->tableName}_table";
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}.php";

        $migrationsPath = base_path("modules/{$this->moduleStudlyName}/database/migrations");
        $this->ensureDirectoryExists($migrationsPath);

        $path = "{$migrationsPath}/{$filename}";

        // Generate column definitions
        $columnDefinitions = $this->generateMigrationColumns();

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$this->tableName}', function (Blueprint \$table) {
            \$table->id();
{$columnDefinitions}
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$this->tableName}');
    }
};
PHP;

        File::put($path, $content);
        $this->migrationCreated = true;
        $this->info("  Created migration: {$filename}");
    }

    protected function generateMigrationColumns(): string
    {
        $lines = [];

        foreach ($this->columns as $column) {
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

            $nullable = in_array($column['dbType'], ['text', 'json', 'date', 'datetime']) ? '->nullable()' : '';
            $default = $column['dbType'] === 'boolean' ? '->default(false)' : '';

            $lines[] = "            \$table->{$method}('{$column['name']}'){$nullable}{$default};";
        }

        return implode("\n", $lines);
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
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'json', 'jsonb' => 'textarea',
            default => 'text',
        };
    }

    protected function generateModel(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Models/{$this->modelStudlyName}.php");

        // Skip if model already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Models/{$this->modelStudlyName}.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/model.stub');

        if (! File::exists($stubPath)) {
            $this->createModelStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate fillable array
        $fillable = $this->generateFillableArray();
        $content = str_replace('$FILLABLE$', $fillable, $content);

        // Generate casts array
        $casts = $this->generateCastsArray();
        $content = str_replace('$CASTS$', $casts, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Models/{$this->modelStudlyName}.php");
    }

    protected function generateDatatable(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Livewire/Components/{$this->modelStudlyName}Datatable.php");

        // Skip if datatable already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Components/{$this->modelStudlyName}Datatable.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/datatable.stub');

        if (! File::exists($stubPath)) {
            $this->createDatatableStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate headers array
        $headers = $this->generateDatatableHeaders();
        $content = str_replace('$HEADERS$', $headers, $content);

        // Generate search query
        $searchQuery = $this->generateSearchQuery();
        $content = str_replace('$SEARCH_QUERY$', $searchQuery, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Components/{$this->modelStudlyName}Datatable.php");
    }

    protected function generateIndexComponent(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Index.php");

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Index.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/index.stub');

        if (! File::exists($stubPath)) {
            $this->createIndexStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Index.php");
    }

    protected function generateShowComponent(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Show.php");

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Show.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/show.stub');

        if (! File::exists($stubPath)) {
            $this->createShowStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Show.php");
    }

    protected function generateCreateComponent(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Create.php");

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Create.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/create.stub');

        if (! File::exists($stubPath)) {
            $this->createCreateStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate properties
        $properties = $this->generateFormProperties();
        $content = str_replace('$PROPERTIES$', $properties, $content);

        // Generate rules
        $rules = $this->generateValidationRules();
        $content = str_replace('$RULES$', $rules, $content);

        // Generate create data
        $createData = $this->generateCreateData();
        $content = str_replace('$CREATE_DATA$', $createData, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: Livewire/Admin/{$this->modelPluralName}/Create.php");
    }

    protected function generateEditComponent(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/app/Livewire/Admin/{$this->modelPluralName}/Edit.php");

        // Skip if component already exists
        if (File::exists($path)) {
            $this->line("  Skipped: Livewire/Admin/{$this->modelPluralName}/Edit.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/edit.stub');

        if (! File::exists($stubPath)) {
            $this->createEditStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate properties
        $properties = $this->generateFormProperties();
        $content = str_replace('$PROPERTIES$', $properties, $content);

        // Generate rules
        $rules = $this->generateValidationRules();
        $content = str_replace('$RULES$', $rules, $content);

        // Generate mount assignments
        $mountAssignments = $this->generateMountAssignments();
        $content = str_replace('$MOUNT_ASSIGNMENTS$', $mountAssignments, $content);

        // Generate update data
        $updateData = $this->generateUpdateData();
        $content = str_replace('$UPDATE_DATA$', $updateData, $content);

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
        $path = base_path("modules/{$this->moduleStudlyName}/resources/views/layouts/crud.blade.php");

        // Skip if layout already exists
        if (File::exists($path)) {
            $this->line('  Skipped: views/layouts/crud.blade.php (already exists)');

            return;
        }

        $content = <<<'BLADE'
@extends('$LOWER_NAME$::layouts.master')

@section('$LOWER_NAME$-admin-content')
    <div>
        {{-- Breadcrumbs are rendered by the page component --}}
        {{ $slot }}
    </div>
@endsection
BLADE;

        $content = $this->replaceTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info('  Created: views/layouts/crud.blade.php');
    }

    protected function generateIndexView(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/index.blade.php");

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/index.blade.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/views/index.stub');

        if (! File::exists($stubPath)) {
            $this->createIndexViewStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/index.blade.php");
    }

    protected function generateShowView(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/show.blade.php");

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/show.blade.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/views/show.stub');

        if (! File::exists($stubPath)) {
            $this->createShowViewStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate display fields
        $displayFields = $this->generateDisplayFields();
        $content = str_replace('$DISPLAY_FIELDS$', $displayFields, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/show.blade.php");
    }

    protected function generateCreateView(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/create.blade.php");

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/create.blade.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/views/create.stub');

        if (! File::exists($stubPath)) {
            $this->createCreateViewStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate form fields
        $formFields = $this->generateFormFields();
        $content = str_replace('$FORM_FIELDS$', $formFields, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/create.blade.php");
    }

    protected function generateEditView(): void
    {
        $path = base_path("modules/{$this->moduleStudlyName}/resources/views/livewire/admin/{$this->modelPluralLower}/edit.blade.php");

        // Skip if view already exists
        if (File::exists($path)) {
            $this->line("  Skipped: views/livewire/admin/{$this->modelPluralLower}/edit.blade.php (already exists)");

            return;
        }

        $stubPath = base_path('stubs/nwidart-stubs/crud/views/edit.stub');

        if (! File::exists($stubPath)) {
            $this->createEditViewStub();
        }

        $content = File::get($stubPath);
        $content = $this->replaceTokens($content);

        // Generate form fields
        $formFields = $this->generateFormFields();
        $content = str_replace('$FORM_FIELDS$', $formFields, $content);

        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("  Created: views/livewire/admin/{$this->modelPluralLower}/edit.blade.php");
    }

    protected function updateRoutes(): void
    {
        $routesPath = base_path("modules/{$this->moduleStudlyName}/routes/web.php");

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
        $menuServicePath = base_path("modules/{$this->moduleStudlyName}/app/Services/MenuService.php");

        // Also check for module-specific naming
        if (! File::exists($menuServicePath)) {
            $menuServicePath = base_path("modules/{$this->moduleStudlyName}/app/Services/{$this->moduleStudlyName}MenuService.php");
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
            $fillable[] = "'{$column['name']}'";
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

                $headers[] = <<<PHP
[
                'id' => '{$column['name']}',
                'title' => __('{$label}'),
                'sortable' => true,
                'sortBy' => '{$column['name']}',{$searchableStr}{$widthStr}
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
            $properties[] = "public {$type} \${$column['name']} = {$default};";
        }

        return implode("\n\n    ", $properties);
    }

    protected function getPhpPropertyType(array $column): string
    {
        return match ($column['type']) {
            'number' => 'int',
            'checkbox' => 'bool',
            default => 'string',
        };
    }

    protected function getPropertyDefault(array $column): string
    {
        return match ($column['type']) {
            'number' => '0',
            'checkbox' => 'false',
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
            $rules[] = "'{$column['name']}' => '{$rule}'";
        }

        return implode(",\n            ", $rules).',';
    }

    protected function getValidationRule(array $column): string
    {
        $rules = [];

        // String/text columns are typically required unless they have nullable in the migration
        // Foreign keys are also required
        if (in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger'])) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Add type-specific rules
        match ($column['type']) {
            'text' => $rules[] = 'string|max:255',
            'textarea' => $rules[] = 'string|max:1000',
            'number' => $rules[] = 'integer|min:0',
            'checkbox' => $rules[] = 'boolean',
            'date' => $rules[] = 'date',
            'datetime' => $rules[] = 'date',
            'select' => null,
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
            $data[] = "'{$column['name']}' => \$validated['{$column['name']}']";
        }

        return implode(",\n            ", $data).',';
    }

    protected function generateUpdateData(): string
    {
        return $this->generateCreateData();
    }

    protected function generateMountAssignments(): string
    {
        if (empty($this->columns)) {
            return "\$this->name = \$this->{$this->modelLowerName}->name;";
        }

        $assignments = [];
        foreach ($this->columns as $column) {
            if ($column['type'] === 'checkbox') {
                // Boolean fields need explicit cast to avoid int-to-bool type error
                $assignments[] = "\$this->{$column['name']} = (bool) \$this->{$this->modelLowerName}->{$column['name']};";
            } elseif ($column['type'] === 'text' || $column['type'] === 'textarea') {
                $assignments[] = "\$this->{$column['name']} = \$this->{$this->modelLowerName}->{$column['name']} ?? '';";
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

        return <<<BLADE
<div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('{$label}') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ \$this->{$this->modelLowerName}->{$column['name']} }}</dd>
                </div>
BLADE;
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
        $isRequired = in_array($column['dbType'], ['string', 'char', 'text', 'mediumText', 'longText'])
            || str_ends_with($column['name'], '_id')
            || in_array($column['dbType'], ['integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger']);
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
            'checkbox' => <<<BLADE
<label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="{$column['name']}" class="form-checkbox">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('{$label}') }}</span>
                    </div>
                </label>
BLADE,
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

    // Stub creation methods

    protected function createModelStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $MODEL_NAME$ extends Model
{
    use HasFactory;

    protected $table = '$TABLE_NAME$';

    protected $fillable = [
        $FILLABLE$
    ];

    protected function casts(): array
    {
        return [
            $CASTS$
        ];
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/model.stub'), $stub);
    }

    protected function createDatatableStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Livewire\Components;

use App\Livewire\Datatable\Datatable;
use Illuminate\Database\Eloquent\Model;
use $MODULE_NAMESPACE$\$STUDLY_NAME$\Models\$MODEL_NAME$;
use Spatie\QueryBuilder\QueryBuilder;

class $MODEL_NAME$Datatable extends Datatable
{
    public string $model = $MODEL_NAME$::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search $MODEL_PLURAL_LOWER$...');
    }

    protected function getHeaders(): array
    {
        return [
            $HEADERS$
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.create',
            'view' => 'admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.show',
            'edit' => 'admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.edit',
            'delete' => 'livewire', // Marker for Livewire-based delete (handled by handleRowDelete)
        ];
    }

    /**
     * Return empty string for delete URL since deletion is handled by Livewire.
     */
    public function getDeleteRouteUrl($item): string
    {
        return '';
    }

    /**
     * Define permissions for action buttons.
     * Set to true to allow all users, or use permission checks like:
     *   'view' => auth()->user()->can('$MODEL_LOWER$.view', $item),
     *   'edit' => auth()->user()->can('$MODEL_LOWER$.edit', $item),
     *   'delete' => auth()->user()->can('$MODEL_LOWER$.delete', $item),
     */
    public function getActionCellPermissions($item): array
    {
        return [
            'view' => true,   // TODO: Add permission check, e.g., auth()->user()->can('$MODEL_LOWER$.view', $item)
            'edit' => true,   // TODO: Add permission check, e.g., auth()->user()->can('$MODEL_LOWER$.edit', $item)
            'delete' => true, // TODO: Add permission check, e.g., auth()->user()->can('$MODEL_LOWER$.delete', $item)
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        return QueryBuilder::for($MODEL_NAME$::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $SEARCH_QUERY$
                });
            })
            ->orderBy($this->sort, $this->direction);
    }

    public function handleRowDelete(Model|$MODEL_NAME$ $item): bool
    {
        return $item->delete();
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/datatable.stub'), $stub);
    }

    protected function createIndexStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Livewire\Admin\$MODEL_PLURAL$;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('$LOWER_NAME$::layouts.crud')]
class Index extends Component
{
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            'title' => __('$MODEL_PLURAL$'),
            'icon' => 'lucide:list',
            'action' => [
                'url' => route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.create'),
                'label' => __('New $MODEL_NAME$'),
                'icon' => 'lucide:plus',
            ],
        ];
    }

    public function render(): View
    {
        return view('$LOWER_NAME$::livewire.admin.$MODEL_PLURAL_LOWER$.index');
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/index.stub'), $stub);
    }

    protected function createShowStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Livewire\Admin\$MODEL_PLURAL$;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use $MODULE_NAMESPACE$\$STUDLY_NAME$\Models\$MODEL_NAME$;

#[Layout('$LOWER_NAME$::layouts.crud')]
class Show extends Component
{
    public $MODEL_NAME$ $$MODEL_LOWER$;

    public array $breadcrumbs = [];

    public function mount($MODEL_NAME$ $$MODEL_LOWER$): void
    {
        $this->$MODEL_LOWER$ = $$MODEL_LOWER$;

        $this->breadcrumbs = [
            'title' => __('View $MODEL_NAME$'),
            'icon' => 'lucide:list',
            'back_url' => route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index'),
            'action' => [
                'url' => route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.edit', $this->$MODEL_LOWER$),
                'label' => __('Edit'),
                'icon' => 'lucide:pencil',
            ],
        ];
    }

    public function render(): View
    {
        return view('$LOWER_NAME$::livewire.admin.$MODEL_PLURAL_LOWER$.show');
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/show.stub'), $stub);
    }

    protected function createCreateStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Livewire\Admin\$MODEL_PLURAL$;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use $MODULE_NAMESPACE$\$STUDLY_NAME$\Models\$MODEL_NAME$;

#[Layout('$LOWER_NAME$::layouts.crud')]
class Create extends Component
{
    $PROPERTIES$

    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            'title' => __('Create $MODEL_NAME$'),
            'icon' => 'lucide:list',
            'back_url' => route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index'),
        ];
    }

    protected function rules(): array
    {
        return [
            $RULES$
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $item = $MODEL_NAME$::create([
            $CREATE_DATA$
        ]);

        session()->flash('success', __('$MODEL_NAME$ created successfully.'));

        $this->redirect(route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.edit', $item), navigate: true);
    }

    public function render(): View
    {
        return view('$LOWER_NAME$::livewire.admin.$MODEL_PLURAL_LOWER$.create');
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/create.stub'), $stub);
    }

    protected function createEditStub(): void
    {
        $stub = <<<'STUB'
<?php

declare(strict_types=1);

namespace $MODULE_NAMESPACE$\$STUDLY_NAME$\Livewire\Admin\$MODEL_PLURAL$;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use $MODULE_NAMESPACE$\$STUDLY_NAME$\Models\$MODEL_NAME$;

#[Layout('$LOWER_NAME$::layouts.crud')]
class Edit extends Component
{
    public $MODEL_NAME$ $$MODEL_LOWER$;

    $PROPERTIES$

    public array $breadcrumbs = [];

    protected function rules(): array
    {
        return [
            $RULES$
        ];
    }

    public function mount($MODEL_NAME$ $$MODEL_LOWER$): void
    {
        $this->$MODEL_LOWER$ = $$MODEL_LOWER$;
        $MOUNT_ASSIGNMENTS$

        $this->breadcrumbs = [
            'title' => __('Edit $MODEL_NAME$'),
            'icon' => 'lucide:list',
            'back_url' => route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index'),
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->$MODEL_LOWER$->update([
            $UPDATE_DATA$
        ]);

        session()->flash('success', __('$MODEL_NAME$ updated successfully.'));
    }

    public function delete(): void
    {
        $this->$MODEL_LOWER$->delete();

        session()->flash('success', __('$MODEL_NAME$ deleted successfully.'));

        $this->redirect(route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index'), navigate: true);
    }

    public function render(): View
    {
        return view('$LOWER_NAME$::livewire.admin.$MODEL_PLURAL_LOWER$.edit');
    }
}
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud'));
        File::put(base_path('stubs/nwidart-stubs/crud/edit.stub'), $stub);
    }

    protected function createIndexViewStub(): void
    {
        $stub = <<<'STUB'
<div>
    {{-- Breadcrumbs Header --}}
    <x-breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    {{-- Datatable --}}
    <livewire:$LOWER_NAME$::components.$MODEL_LOWER$-datatable />
</div>
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud/views'));
        File::put(base_path('stubs/nwidart-stubs/crud/views/index.stub'), $stub);
    }

    protected function createShowViewStub(): void
    {
        $stub = <<<'STUB'
<div>
    {{-- Breadcrumbs Header --}}
    <x-breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    {{-- Details --}}
    <x-card.card>
        <x-slot name="header">{{ __('$MODEL_NAME$ Information') }}</x-slot>

        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            $DISPLAY_FIELDS$
        </dl>
    </x-card.card>
</div>
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud/views'));
        File::put(base_path('stubs/nwidart-stubs/crud/views/show.stub'), $stub);
    }

    protected function createCreateViewStub(): void
    {
        $stub = <<<'STUB'
<div>
    {{-- Breadcrumbs Header --}}
    <x-breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    {{-- Form --}}
    <form wire:submit="save" class="space-y-6">
        <x-card.card>
            <x-slot name="header">{{ __('$MODEL_NAME$ Information') }}</x-slot>

            <div class="space-y-6">
                $FORM_FIELDS$
            </div>
        </x-card.card>

        {{-- Submit --}}
        <div class="flex items-center gap-4">
            <a wire:navigate href="{{ route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index') }}" class="btn-default">
                {{ __('Cancel') }}
            </a>
            <x-buttons.button type="submit" variant="primary" icon="lucide:save" loadingTarget="save">
                {{ __('Create $MODEL_NAME$') }}
            </x-buttons.button>
        </div>
    </form>
</div>
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud/views'));
        File::put(base_path('stubs/nwidart-stubs/crud/views/create.stub'), $stub);
    }

    protected function createEditViewStub(): void
    {
        $stub = <<<'STUB'
<div>
    {{-- Breadcrumbs Header --}}
    <x-breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    {{-- Form --}}
    <form wire:submit="save" class="space-y-6">
        <x-card.card>
            <x-slot name="header">{{ __('$MODEL_NAME$ Information') }}</x-slot>

            <div class="space-y-6">
                $FORM_FIELDS$
            </div>
        </x-card.card>

        {{-- Submit --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a wire:navigate href="{{ route('admin.$LOWER_NAME$.$MODEL_PLURAL_LOWER$.index') }}" class="btn-default">
                    {{ __('Cancel') }}
                </a>
                <x-buttons.button type="submit" variant="primary" icon="lucide:save" loadingTarget="save">
                    {{ __('Save Changes') }}
                </x-buttons.button>
            </div>

            <x-buttons.button
                type="button"
                variant="danger"
                icon="lucide:trash-2"
                wire:click="delete"
                wire:confirm="{{ __('Are you sure you want to delete this $MODEL_LOWER$?') }}"
                loadingTarget="delete"
            >
                {{ __('Delete') }}
            </x-buttons.button>
        </div>
    </form>
</div>
STUB;

        $this->ensureDirectoryExists(base_path('stubs/nwidart-stubs/crud/views'));
        File::put(base_path('stubs/nwidart-stubs/crud/views/edit.stub'), $stub);
    }
}
