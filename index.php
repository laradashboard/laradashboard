<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * This file allows Laravel to work on shared hosting (cPanel)
 * without changing the document root to the public directory.
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */

// Set the public path to this directory
$publicPath = __DIR__.'/public';
$basePath = __DIR__;

// Auto-create .env from .env.example if it doesn't exist
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

// Check if the request is for a file in the public directory
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Remove query string and leading slash
$uri = ltrim($uri, '/');

// If the file exists in public directory, serve it directly
if ($uri !== '' && file_exists($publicPath.'/'.$uri)) {
    // Let .htaccess handle static files, or return false for PHP's built-in server
    return false;
}

// Otherwise, load the Laravel application
require $publicPath.'/index.php';
