<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$basePath = __DIR__.'/..';

/*
|--------------------------------------------------------------------------
| Auto-Create Environment File
|--------------------------------------------------------------------------
|
| If .env doesn't exist but .env.example does, automatically create .env
| with a generated APP_KEY. This allows fresh uploads to work without
| manual configuration for the initial setup.
|
*/

$envFile = $basePath.'/.env';
$envExampleFile = $basePath.'/.env.example';

if (! file_exists($envFile) && file_exists($envExampleFile)) {
    // Copy .env.example to .env
    $envContent = file_get_contents($envExampleFile);

    // Generate a random APP_KEY
    $key = 'base64:'.base64_encode(random_bytes(32));
    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $envContent);

    // Set APP_ENV to production for fresh installs
    $envContent = preg_replace('/^APP_ENV=.*$/m', 'APP_ENV=production', $envContent);

    // Disable debug mode for security
    $envContent = preg_replace('/^APP_DEBUG=.*$/m', 'APP_DEBUG=false', $envContent);

    // Write the new .env file
    file_put_contents($envFile, $envContent);

    // Create storage directories if they don't exist
    $storageDirs = [
        $basePath.'/storage/app',
        $basePath.'/storage/app/public',
        $basePath.'/storage/framework',
        $basePath.'/storage/framework/cache',
        $basePath.'/storage/framework/cache/data',
        $basePath.'/storage/framework/sessions',
        $basePath.'/storage/framework/views',
        $basePath.'/storage/logs',
    ];

    foreach ($storageDirs as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // Create bootstrap/cache if it doesn't exist
    $bootstrapCache = $basePath.'/bootstrap/cache';
    if (! is_dir($bootstrapCache)) {
        mkdir($bootstrapCache, 0755, true);
    }
}

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
