<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeCrudCommand extends Command
{
    protected $signature = 'ld:make-crud 
        {--table_name= : The table name or migration name}
        {--resource= : The resource name (optional)}
        {--module-name= : The module name (optional)}
        {--api : Generate API controller and routes}
        {--menu : Update ServiceProvider to generate menu}';

    protected $description = 'Generate CRUD files for a resource based on table name';

    public function handle()
    {
        $table = $this->option('table_name');
        $resource = $this->option('resource') ?? $table;
        $module = $this->option('module-name');
        $api = $this->option('api');
        $menu = $this->option('menu');

        if (! $table) {
            $this->error('You must provide --table_name');
            return 1;
        }

        // 1. Get columns from the table
        if (! Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist.");
            return 1;
        }
        $columns = Schema::getColumnListing($table);

        // 2. Prepare stub replacements
        $modelName = Str::studly(Str::singular($resource));
        $controllerName = $modelName . 'Controller';
        $resourceClass = $modelName . 'Resource';
        $apiControllerName = $api ? "Api/{$controllerName}" : null;
        $fillable = collect($columns)->filter(fn ($col) => $col !== 'id' && $col !== 'created_at' && $col !== 'updated_at')->map(fn ($col) => "'$col'")->implode(', ');

        $stubVars = [
            '{{modelName}}' => $modelName,
            '{{controllerName}}' => $controllerName,
            '{{resourceName}}' => $resource,
            '{{tableName}}' => $table,
            '{{fillable}}' => $fillable,
            '{{resourceClass}}' => $resourceClass,
        ];

        // 3. Generate Model
        $modelStub = File::get(base_path('stubs/crud/model.stub'));
        $modelContent = str_replace(array_keys($stubVars), array_values($stubVars), $modelStub);
        $modelPath = app_path("Models/{$modelName}.php");
        File::put($modelPath, $modelContent);

        // 4. Generate Controller
        $controllerStub = File::get(base_path('stubs/crud/controller.stub'));
        $controllerContent = str_replace(array_keys($stubVars), array_values($stubVars), $controllerStub);
        $controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        File::put($controllerPath, $controllerContent);

        // 5. Generate Resource class
        $resourceStub = File::get(base_path('stubs/crud/resource.stub'));
        $resourceContent = str_replace(array_keys($stubVars), array_values($stubVars), $resourceStub);

        // Prepare resource fields for Resource class
        $resourceFields = "            'id' => \$this->id,\n";
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            $resourceFields .= "            '{$col}' => \$this->{$col},\n";
        }
        $resourceFields .= "            'created_at' => \$this->created_at,\n";
        $resourceFields .= "            'updated_at' => \$this->updated_at,\n";
        $resourceContent = str_replace('{{resourceFields}}', $resourceFields, $resourceContent);

        $resourcePath = app_path("Http/Resources/{$resourceClass}.php");
        File::put($resourcePath, $resourceContent);

        // 6. Generate API Controller if requested
        if ($api) {
            $apiControllerStub = File::get(base_path('stubs/crud/api_controller.stub'));
            $apiControllerContent = str_replace(array_keys($stubVars), array_values($stubVars), $apiControllerStub);
            $apiControllerPath = app_path("Http/Controllers/Api/{$controllerName}.php");
            File::ensureDirectoryExists(app_path("Http/Controllers/Api"));
            File::put($apiControllerPath, $apiControllerContent);
        }

        // Prepare fillable columns for views (with type detection)
        $formFields = '';
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            $type = Schema::getColumnType($table, $col);
            $inputType = $type === 'integer' ? 'number' : ($type === 'boolean' ? 'checkbox' : 'text');
            $value = "{{ old('{$col}'" . ($inputType !== 'checkbox' ? ", isset(\$item) ? \$item->{$col} : ''" : '') . ") }}";
            $formFields .= <<<BLADE
<div class="mb-4">
    <label for="{$col}" class="form-label">{{ __('{$col}') }}</label>
    <input type="{$inputType}" name="{$col}" id="{$col}" class="form-control" value="{$value}">
</div>

BLADE;
        }

        $showFields = '';
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            $showFields .= <<<BLADE
<div class="mb-2">
    <strong>{{ __('{$col}') }}:</strong> {{ \$item->{$col} }}
</div>

BLADE;
        }

        $tableHeaders = '';
        $tableRows = '';
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            $tableHeaders .= "<th>{{ __('{$col}') }}</th>\n";
            $tableRows .= "<td>{{ \$item->{$col} }}</td>\n";
        }

        // 7. Generate views in backend/pages/{resourceName}
        $views = ['index', 'create', 'edit', 'show'];
        $viewDir = resource_path("views/backend/pages/{$resource}");
        File::ensureDirectoryExists($viewDir);
        foreach ($views as $view) {
            $viewStub = File::get(base_path("stubs/crud/views/{$view}.stub"));
            $viewContent = str_replace(array_keys($stubVars), array_values($stubVars), $viewStub);

            // Inject dynamic fields
            if ($view === 'create' || $view === 'edit') {
                $viewContent = str_replace('{{fields}}', $formFields, $viewContent);
            }
            if ($view === 'show') {
                $viewContent = str_replace('{{fields}}', $showFields, $viewContent);
            }
            if ($view === 'index') {
                $viewContent = str_replace(['{{tableHeaders}}', '{{tableRows}}'], [$tableHeaders, $tableRows], $viewContent);
            }

            File::put("{$viewDir}/{$view}.blade.php", $viewContent);
        }

        // 8. Update routes
        $webRoute = "\nRoute::resource('{$resource}', \\App\\Http\\Controllers\\{$controllerName}::class);";
        File::append(base_path('routes/web.php'), $webRoute);

        if ($api) {
            $apiRoute = "\nRoute::apiResource('{$resource}', \\App\\Http\\Controllers\\Api\\{$controllerName}::class);";
            File::append(base_path('routes/api.php'), $apiRoute);
        }

        // 9. Update ServiceProvider for menu (if --menu)
        if ($menu) {
            // You may want to append menu registration code to your ServiceProvider here.
            // Example: File::append(app_path('Providers/ModuleServiceProvider.php'), "\n// Add menu for {$resource}");
        }

        $this->info("CRUD for {$resource} generated successfully.");
        return 0;
    }
}
