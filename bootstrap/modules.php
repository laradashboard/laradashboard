<?php

/**
 * Safe Module Loader
 *
 * This file runs before Laravel boots to validate modules and auto-disable broken ones.
 * This prevents the entire application from crashing due to a single broken module.
 */

(function () {
    $modulesPath = dirname(__DIR__) . '/modules';
    $statusesPath = dirname(__DIR__) . '/modules_statuses.json';
    $vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

    if (! file_exists($statusesPath)) {
        return;
    }

    $statuses = json_decode(file_get_contents($statusesPath), true);

    if (! is_array($statuses)) {
        return;
    }

    // Load Composer autoloader for class_exists checks
    if (! file_exists($vendorAutoload)) {
        return;
    }

    $loader = require $vendorAutoload;

    if (! is_dir($modulesPath)) {
        return;
    }

    // Build a case-insensitive map of actual module directories
    $actualDirs = [];
    foreach (scandir($modulesPath) as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }
        $fullPath = $modulesPath . '/' . $dir;
        if (is_dir($fullPath) && file_exists($fullPath . '/module.json')) {
            $actualDirs[strtolower($dir)] = $dir;
        }
    }

    $modified = false;
    $disabledModules = [];
    $validatedModules = []; // Track which modules passed validation

    foreach ($statuses as $moduleName => $isEnabled) {
        if (! $isEnabled) {
            continue;
        }

        // Find the actual directory (case-insensitive match)
        $actualDir = $actualDirs[strtolower($moduleName)] ?? null;

        if (! $actualDir) {
            // Module directory not found
            $statuses[$moduleName] = false;
            $modified = true;
            $disabledModules[$moduleName] = 'Module directory not found';

            continue;
        }

        $moduleDir = $modulesPath . '/' . $actualDir;
        $moduleJsonPath = $moduleDir . '/module.json';
        $composerJson = $moduleDir . '/composer.json';

        // Parse module.json and validate
        $moduleConfig = json_decode(file_get_contents($moduleJsonPath), true);

        if (! is_array($moduleConfig)) {
            $statuses[$moduleName] = false;
            $modified = true;
            $disabledModules[$moduleName] = 'Invalid module.json';

            continue;
        }

        // Register PSR-4 namespaces for this module ONLY
        try {
            if (file_exists($composerJson)) {
                $composerConfig = json_decode(file_get_contents($composerJson), true);
                if (is_array($composerConfig) && ! empty($composerConfig['autoload']['psr-4'])) {
                    foreach ($composerConfig['autoload']['psr-4'] as $namespace => $paths) {
                        $paths = is_array($paths) ? $paths : [$paths];
                        foreach ($paths as $path) {
                            $fullPath = $moduleDir . '/' . ltrim($path, '/');
                            if (is_dir($fullPath) || $path === '' || $path === './') {
                                $loader->addPsr4($namespace, $path === '' || $path === './' ? $moduleDir . '/' : $fullPath);
                            }
                        }
                    }
                }
            } else {
                // Fallback: use module.json name and guess paths
                if (! empty($moduleConfig['name'])) {
                    // Extract namespace from providers if available
                    $namespace = null;
                    if (! empty($moduleConfig['providers'])) {
                        foreach ($moduleConfig['providers'] as $provider) {
                            if (preg_match('/^(Modules\\\\[^\\\\]+)\\\\/', $provider, $matches)) {
                                $namespace = $matches[1] . '\\';

                                break;
                            }
                        }
                    }
                    if (! $namespace) {
                        $namespace = 'Modules\\' . ucfirst($moduleConfig['name']) . '\\';
                    }

                    // Try common paths for PSR-4 root
                    $possibleRoots = [$moduleDir . '/app/', $moduleDir . '/src/', $moduleDir . '/'];
                    foreach ($possibleRoots as $root) {
                        if (is_dir($root)) {
                            $loader->addPsr4($namespace, $root);

                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $statuses[$moduleName] = false;
            $modified = true;
            $disabledModules[$moduleName] = 'Failed to register autoloader: ' . $e->getMessage();

            continue;
        }

        // Check if provider files exist (don't use class_exists as it triggers autoloading
        // which can fail before Laravel's container is ready)
        if (! empty($moduleConfig['providers'])) {
            $providerValid = true;
            foreach ($moduleConfig['providers'] as $provider) {
                // Convert provider class to file path
                // e.g. Modules\Crm\Providers\CrmServiceProvider -> Providers/CrmServiceProvider.php
                $providerPath = null;

                // Extract the path after the module namespace
                if (preg_match('/^Modules\\\\[^\\\\]+\\\\(.+)$/', $provider, $matches)) {
                    $relativePath = str_replace('\\', '/', $matches[1]) . '.php';
                    $providerPath = $moduleDir . '/app/' . $relativePath;
                }

                if (! $providerPath || ! file_exists($providerPath)) {
                    $statuses[$moduleName] = false;
                    $modified = true;
                    $disabledModules[$moduleName] = "Provider file not found: {$provider}";
                    $providerValid = false;

                    break;
                }
            }

            if (! $providerValid) {
                continue;
            }
        }

        // Module passed validation - mark for vendor loading
        $validatedModules[$moduleName] = $moduleDir;
    }

    // Note: Module vendor autoloaders are NOT loaded here.
    // They should be loaded by the module's service provider after Laravel boots.
    // Loading them here can cause conflicts with the main app's dependencies.

    // Save updated statuses if any modules were disabled
    if ($modified) {
        file_put_contents($statusesPath, json_encode($statuses, JSON_PRETTY_PRINT));

        // Store disabled modules for later notification
        $disabledPath = dirname(__DIR__) . '/storage/framework/modules_auto_disabled.json';
        @file_put_contents($disabledPath, json_encode($disabledModules, JSON_PRETTY_PRINT));
    }
})();
