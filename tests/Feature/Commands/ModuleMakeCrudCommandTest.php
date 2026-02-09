<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->moduleName = 'TestCrud';
    // Module paths can be in either PascalCase or kebab-case depending on version
    $this->modulePath = base_path('modules/test-crud');
    $this->modulePathAlt = base_path("modules/{$this->moduleName}");

    // Clean up any existing test module (both possible paths)
    foreach ([$this->modulePath, $this->modulePathAlt] as $path) {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }
});

afterEach(function () {
    // Clean up the test module after each test (both possible paths)
    foreach ([$this->modulePath, $this->modulePathAlt] as $path) {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }
});

test('crud command requires module argument', function () {
    $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "module")');

    $this->artisan('module:make-crud');
});

test('crud command fails without migration or model', function () {
    // First create the module
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    // Try to run CRUD without migration or model
    $this->artisan('module:make-crud', ['module' => $this->moduleName])
        ->assertFailed();
});

test('crud command generates files with fields option', function () {
    // First create the module
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    // Run CRUD command with fields
    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Book',
        '--fields' => 'title:string,description:text,price:decimal,is_active:boolean',
    ])->assertSuccessful();

    // Assert model was created
    expect(File::exists("{$this->modulePath}/app/Models/Book.php"))->toBeTrue();

    // Assert Livewire components were created
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Books/Index.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Books/Create.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Books/Edit.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Books/Show.php"))->toBeTrue();

    // Assert Datatable was created
    expect(File::exists("{$this->modulePath}/app/Livewire/Components/BookDatatable.php"))->toBeTrue();

    // Assert views were created
    expect(File::exists("{$this->modulePath}/resources/views/livewire/admin/books/index.blade.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/resources/views/livewire/admin/books/create.blade.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/resources/views/livewire/admin/books/edit.blade.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/resources/views/livewire/admin/books/show.blade.php"))->toBeTrue();

    // Assert migration was created
    $migrations = File::glob("{$this->modulePath}/database/migrations/*_create_testcrud_books_table.php");
    expect($migrations)->not->toBeEmpty();
});

test('crud command generates model with correct fillable', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Product',
        '--fields' => 'name:string,description:text,price:decimal,stock:integer',
    ])->assertSuccessful();

    $modelContent = File::get("{$this->modulePath}/app/Models/Product.php");

    // Check fillable fields
    expect($modelContent)->toContain("'name'");
    expect($modelContent)->toContain("'description'");
    expect($modelContent)->toContain("'price'");
    expect($modelContent)->toContain("'stock'");

    // Check table name
    expect($modelContent)->toContain("protected \$table = 'testcrud_products'");
});

test('crud command generates correct field types in migration', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Item',
        '--fields' => 'title:string,content:text,price:decimal,quantity:integer,is_active:boolean,release_date:date,published_at:datetime',
    ])->assertSuccessful();

    $migrations = File::glob("{$this->modulePath}/database/migrations/*_create_testcrud_items_table.php");
    expect($migrations)->not->toBeEmpty();

    $migrationContent = File::get($migrations[0]);

    // Check column types
    expect($migrationContent)->toContain("->string('title')");
    expect($migrationContent)->toContain("->text('content')");
    expect($migrationContent)->toContain("->decimal('price'");
    expect($migrationContent)->toContain("->integer('quantity')");
    expect($migrationContent)->toContain("->boolean('is_active')");
    expect($migrationContent)->toContain("->date('release_date')");
    expect($migrationContent)->toContain("->dateTime('published_at')");
});

test('crud command generates toggle field', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Article',
        '--fields' => 'title:string,is_featured:toggle',
    ])->assertSuccessful();

    // Check that the create view has toggle component
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/articles/create.blade.php");
    expect($createViewContent)->toContain('x-inputs.toggle');
    expect($createViewContent)->toContain('is_featured');

    // Check model has boolean cast
    $modelContent = File::get("{$this->modulePath}/app/Models/Article.php");
    expect($modelContent)->toContain("'is_featured' => 'boolean'");
});

test('crud command generates select field with options', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Task',
        '--fields' => 'title:string,status:select:Open|In Progress|Closed',
    ])->assertSuccessful();

    // Check that the create view has select component with options
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/tasks/create.blade.php");
    expect($createViewContent)->toContain('x-inputs.select');
    expect($createViewContent)->toContain('status');
    expect($createViewContent)->toContain('Open');
    expect($createViewContent)->toContain('In Progress');
    expect($createViewContent)->toContain('Closed');
});

test('crud command generates editor field', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Post',
        '--fields' => 'title:string,content:editor',
    ])->assertSuccessful();

    // Check that the create view has TinyMCE editor setup
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/posts/create.blade.php");
    expect($createViewContent)->toContain('tinymce');
    expect($createViewContent)->toContain('content');
});

test('crud command generates media field', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Gallery',
        '--fields' => 'title:string,featured_image:media',
    ])->assertSuccessful();

    // Check that migration creates foreign key
    $migrations = File::glob("{$this->modulePath}/database/migrations/*_create_testcrud_galleries_table.php");
    $migrationContent = File::get($migrations[0]);
    expect($migrationContent)->toContain('featured_image_id');
    expect($migrationContent)->toContain('foreignId');

    // Check model has relationship
    $modelContent = File::get("{$this->modulePath}/app/Models/Gallery.php");
    expect($modelContent)->toContain('featuredImage');
    expect($modelContent)->toContain('belongsTo');
    expect($modelContent)->toContain('getFeaturedImageUrlAttribute');

    // Check create view has media-selector
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/galleries/create.blade.php");
    expect($createViewContent)->toContain('x-media-selector');
    expect($createViewContent)->toContain('featured_image_id');

    // Check Livewire component has nullable int property
    $createComponentContent = File::get("{$this->modulePath}/app/Livewire/Admin/Galleries/Create.php");
    expect($createComponentContent)->toContain('public ?int $featured_image_id = null');
});

test('crud command generates json field', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Config',
        '--fields' => 'key:string,metadata:json',
    ])->assertSuccessful();

    // Check migration has json column
    $migrations = File::glob("{$this->modulePath}/database/migrations/*_create_testcrud_configs_table.php");
    $migrationContent = File::get($migrations[0]);
    expect($migrationContent)->toContain("->json('metadata')");

    // Check model has array cast
    $modelContent = File::get("{$this->modulePath}/app/Models/Config.php");
    expect($modelContent)->toContain("'metadata' => 'array'");
});

test('crud command generates datatable with searchable columns', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Customer',
        '--fields' => 'name:string,email:string,phone:string,notes:text',
    ])->assertSuccessful();

    $datatableContent = File::get("{$this->modulePath}/app/Livewire/Components/CustomerDatatable.php");

    // Check searchable columns
    expect($datatableContent)->toContain("'searchable' => true");
    expect($datatableContent)->toContain("->where('name', 'like'");
    expect($datatableContent)->toContain("->orWhere('email', 'like'");
});

test('crud command generates routes', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Event',
        '--fields' => 'title:string,date:date',
    ])->assertSuccessful();

    $routesContent = File::get("{$this->modulePath}/routes/web.php");

    // Check routes are added
    expect($routesContent)->toContain("Route::get('events'");
    expect($routesContent)->toContain("Route::get('events/create'");
    expect($routesContent)->toContain("Route::get('events/{event}'");
    expect($routesContent)->toContain("Route::get('events/{event}/edit'");
});

test('crud command skips existing files', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    // Run CRUD command first time
    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Document',
        '--fields' => 'title:string',
    ])->assertSuccessful();

    // Modify the model file
    $modelPath = "{$this->modulePath}/app/Models/Document.php";
    $originalContent = File::get($modelPath);
    File::put($modelPath, $originalContent . "\n// Custom modification");

    // Run CRUD command again
    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Document',
        '--fields' => 'title:string,description:text',
    ])->assertSuccessful();

    // Check that model file was not overwritten
    $newContent = File::get($modelPath);
    expect($newContent)->toContain('// Custom modification');
});

test('crud command handles multi word model names', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'BlogPost',
        '--fields' => 'title:string,content:text',
    ])->assertSuccessful();

    // Assert files are created with correct names
    expect(File::exists("{$this->modulePath}/app/Models/BlogPost.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/BlogPosts/Index.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Components/BlogPostDatatable.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/resources/views/livewire/admin/blogposts/index.blade.php"))->toBeTrue();

    // Check table name uses snake_case
    $modelContent = File::get("{$this->modulePath}/app/Models/BlogPost.php");
    expect($modelContent)->toContain("protected \$table = 'testcrud_blog_posts'");
});

test('crud command generates decimal with correct php type', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Invoice',
        '--fields' => 'number:string,amount:decimal,tax:decimal',
    ])->assertSuccessful();

    // Check Livewire component has float type for decimal fields
    $createComponentContent = File::get("{$this->modulePath}/app/Livewire/Admin/Invoices/Create.php");
    expect($createComponentContent)->toContain('public float $amount = 0.0');
    expect($createComponentContent)->toContain('public float $tax = 0.0');
});

test('crud command generates date fields with correct format', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Appointment',
        '--fields' => 'title:string,appointment_date:date,scheduled_at:datetime',
    ])->assertSuccessful();

    // Check Edit component formats dates correctly
    $editComponentContent = File::get("{$this->modulePath}/app/Livewire/Admin/Appointments/Edit.php");
    expect($editComponentContent)->toContain("->format('Y-m-d')");
    expect($editComponentContent)->toContain("->format('Y-m-d\\TH:i')");

    // Check views use correct input types
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/appointments/create.blade.php");
    expect($createViewContent)->toContain('type="date"');
    expect($createViewContent)->toContain('type="datetime-local"');
});

test('crud command generates datatable with renderable import', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Photo',
        '--fields' => 'title:string,image:media,is_featured:toggle',
    ])->assertSuccessful();

    // Check Datatable has Renderable import for media and toggle columns
    $datatableContent = File::get("{$this->modulePath}/app/Livewire/Components/PhotoDatatable.php");
    expect($datatableContent)->toContain('use Illuminate\\Contracts\\Support\\Renderable');
    expect($datatableContent)->toContain('): Renderable');
});

test('crud command comprehensive example', function () {
    $this->artisan('module:make', ['name' => [$this->moduleName]])
        ->assertSuccessful();

    // This tests the comprehensive example from documentation
    $this->artisan('module:make-crud', [
        'module' => $this->moduleName,
        '--model' => 'Demo',
        '--fields' => 'title:string,description:text,content:editor,featured_image:media,price:decimal,quantity:integer,status:select:Draft|Published|Archived,release_date:date,published_at:datetime,is_featured:toggle,metadata:json',
    ])->assertSuccessful();

    // Assert all files are created
    expect(File::exists("{$this->modulePath}/app/Models/Demo.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Demos/Index.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Demos/Create.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Demos/Edit.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Admin/Demos/Show.php"))->toBeTrue();
    expect(File::exists("{$this->modulePath}/app/Livewire/Components/DemoDatatable.php"))->toBeTrue();

    // Check model has all expected content
    $modelContent = File::get("{$this->modulePath}/app/Models/Demo.php");
    expect($modelContent)->toContain("'title'");
    expect($modelContent)->toContain("'description'");
    expect($modelContent)->toContain("'content'");
    expect($modelContent)->toContain("'featured_image_id'");
    expect($modelContent)->toContain("'price'");
    expect($modelContent)->toContain("'quantity'");
    expect($modelContent)->toContain("'status'");
    expect($modelContent)->toContain("'release_date'");
    expect($modelContent)->toContain("'published_at'");
    expect($modelContent)->toContain("'is_featured'");
    expect($modelContent)->toContain("'metadata'");
    expect($modelContent)->toContain('featuredImage');
    expect($modelContent)->toContain("'is_featured' => 'boolean'");
    expect($modelContent)->toContain("'metadata' => 'array'");
    expect($modelContent)->toContain("'release_date' => 'date'");
    expect($modelContent)->toContain("'published_at' => 'datetime'");

    // Check views have all expected components
    $createViewContent = File::get("{$this->modulePath}/resources/views/livewire/admin/demos/create.blade.php");
    expect($createViewContent)->toContain('x-inputs.input');
    expect($createViewContent)->toContain('x-inputs.select');
    expect($createViewContent)->toContain('x-inputs.toggle');
    expect($createViewContent)->toContain('x-media-selector');
    expect($createViewContent)->toContain('tinymce');
    expect($createViewContent)->toContain('type="date"');
    expect($createViewContent)->toContain('type="datetime-local"');
    expect($createViewContent)->toContain('type="number"');
});
