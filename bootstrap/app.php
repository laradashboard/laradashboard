<?php

/*
|--------------------------------------------------------------------------
| Auto-generate APP_KEY if missing
|--------------------------------------------------------------------------
|
| Ensure APP_KEY exists before Laravel boots to prevent encryption errors.
| This is essential for the installation wizard to work properly.
|
*/

$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);

    // Check if APP_KEY is missing or empty
    if (! preg_match('/^APP_KEY=.+$/m', $envContent) || preg_match('/^APP_KEY=\s*$/m', $envContent)) {
        // Generate a new APP_KEY
        $key = 'base64:' . base64_encode(random_bytes(32));

        // Update or add APP_KEY in .env
        if (preg_match('/^APP_KEY=/m', $envContent)) {
            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY="' . $key . '"', $envContent);
        } else {
            $envContent .= PHP_EOL . 'APP_KEY="' . $key . '"';
        }

        // Write back to .env file
        file_put_contents($envPath, $envContent);

        // Set in environment for current request
        $_ENV['APP_KEY'] = $key;
        $_SERVER['APP_KEY'] = $key;
        putenv('APP_KEY=' . $key);
    }
}

/*
|--------------------------------------------------------------------------
| Safe Module Loader
|--------------------------------------------------------------------------
|
| Validate modules before Laravel boots to prevent broken modules from
| crashing the entire application. This runs before any Laravel code.
|
*/

require __DIR__ . '/modules.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
