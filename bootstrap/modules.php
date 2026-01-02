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
    if (file_exists($vendorAutoload)) {
        $loader = require $vendorAutoload;

        // Register PSR-4 namespaces for all module directories
        if (is_dir($modulesPath)) {
            foreach (scandir($modulesPath) as $dir) {
                if ($dir === '.' || $dir === '..' || ! is_dir($modulesPath . '/' . $dir)) {
                    continue;
                }

                $moduleDir = $modulesPath . '/' . $dir;
                $composerJson = $moduleDir . '/composer.json';
                $moduleJson = $moduleDir . '/module.json';

                // First, try to use the module's composer.json for PSR-4 mappings
                if (file_exists($composerJson)) {
                    $composerConfig = json_decode(file_get_contents($composerJson), true);
                    if (is_array($composerConfig) && ! empty($composerConfig['autoload']['psr-4'])) {
                        foreach ($composerConfig['autoload']['psr-4'] as $namespace => $paths) {
                            // Handle both string and array paths
                            $paths = is_array($paths) ? $paths : [$paths];
                            foreach ($paths as $path) {
                                $fullPath = $moduleDir . '/' . ltrim($path, '/');
                                if (is_dir($fullPath) || $path === '' || $path === './') {
                                    $loader->addPsr4($namespace, $path === '' || $path === './' ? $moduleDir . '/' : $fullPath);
                                }
                            }
                        }

                        continue;
                    }
                }

                // Fallback: use module.json and guess paths
                if (file_exists($moduleJson)) {
                    $config = json_decode(file_get_contents($moduleJson), true);
                    if (is_array($config) && ! empty($config['name'])) {
                        $namespace = 'Modules\\' . $config['name'] . '\\';
                        // Try common paths for PSR-4 root
                        $possibleRoots = [
                            $moduleDir . '/app/',
                            $moduleDir . '/src/',
                            $moduleDir . '/',
                        ];
                        foreach ($possibleRoots as $root) {
                            if (is_dir($root)) {
                                $loader->addPsr4($namespace, $root);

                                break;
                            }
                        }
                    }
                }
            }

            // Register module vendor autoloaders
            // This enables modules to have their own independent dependencies
            foreach (scandir($modulesPath) as $dir) {
                if ($dir === '.' || $dir === '..' || ! is_dir($modulesPath . '/' . $dir)) {
                    continue;
                }

                $moduleVendorPath = $modulesPath . '/' . $dir . '/vendor';
                $moduleVendorAutoload = $moduleVendorPath . '/autoload.php';

                // Check if vendor directory is complete (has installed.php or installed.json)
                // Incomplete vendor directories can cause autoload failures
                $hasInstalledPhp = file_exists($moduleVendorPath . '/composer/installed.php');
                $hasInstalledJson = file_exists($moduleVendorPath . '/composer/installed.json');

                if (file_exists($moduleVendorAutoload) && ($hasInstalledPhp || $hasInstalledJson)) {
                    try {
                        require $moduleVendorAutoload;
                    } catch (\Throwable $e) {
                        // Silently continue - module vendor autoload failed
                        // This will be handled when the module provider is validated
                    }
                }
            }
        }
    }

    $modified = false;
    $disabledModules = [];

    // Build a case-insensitive map of actual module directories
    $actualDirs = [];
    if (is_dir($modulesPath)) {
        foreach (scandir($modulesPath) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $fullPath = $modulesPath . '/' . $dir;
            if (is_dir($fullPath) && file_exists($fullPath . '/module.json')) {
                $actualDirs[strtolower($dir)] = $dir;
            }
        }
    }

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

        // Parse module.json and validate providers
        $moduleConfig = json_decode(file_get_contents($moduleJsonPath), true);

        if (! is_array($moduleConfig)) {
            $statuses[$moduleName] = false;
            $modified = true;
            $disabledModules[$moduleName] = 'Invalid module.json';

            continue;
        }

        // Check if providers can be loaded
        if (! empty($moduleConfig['providers'])) {
            foreach ($moduleConfig['providers'] as $provider) {
                try {
                    // Check if class can be autoloaded
                    if (! class_exists($provider, true)) {
                        $statuses[$moduleName] = false;
                        $modified = true;
                        $disabledModules[$moduleName] = "Provider class not found: {$provider}";

                        break;
                    }
                } catch (\Throwable $e) {
                    $statuses[$moduleName] = false;
                    $modified = true;
                    $disabledModules[$moduleName] = "Error loading provider {$provider}: " . $e->getMessage();

                    break;
                }
            }
        }
    }

    // Save updated statuses if any modules were disabled
    if ($modified) {
        file_put_contents($statusesPath, json_encode($statuses, JSON_PRETTY_PRINT));

        // Store disabled modules for later notification
        $disabledPath = dirname(__DIR__) . '/storage/framework/modules_auto_disabled.json';
        file_put_contents($disabledPath, json_encode($disabledModules, JSON_PRETTY_PRINT));
    }
})();
