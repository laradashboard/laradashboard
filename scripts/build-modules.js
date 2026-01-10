import fs from 'fs/promises';
import path from 'path';
import { execFileSync } from 'child_process';

/**
 * Build Modules Script
 *
 * Usage:
 *   node scripts/build-modules.js                     # Build all enabled modules
 *   node scripts/build-modules.js --modules=all       # Build all enabled modules
 *   node scripts/build-modules.js --modules=crm       # Build only CRM module
 *   node scripts/build-modules.js --modules=crm,customform  # Build specific modules
 */

// Validate module name to prevent shell injection
function isValidModuleName(name) {
  // Only allow alphanumeric, hyphens, underscores (no shell metacharacters)
  return typeof name === 'string' && /^[a-zA-Z0-9_-]+$/.test(name);
}

// Parse command line arguments
function parseArgs() {
  const args = process.argv.slice(2);
  let modules = null; // null means all enabled modules

  for (const arg of args) {
    if (arg.startsWith('--modules=')) {
      const value = arg.split('=')[1];
      if (value === 'all') {
        modules = null; // Build all enabled modules
      } else {
        modules = value.split(',').map(m => m.trim().toLowerCase()).filter(Boolean);
      }
    }
  }

  return { modules };
}

async function getAvailableModules(repoRoot) {
  const modulesDir = path.join(repoRoot, 'modules');
  try {
    const entries = await fs.readdir(modulesDir, { withFileTypes: true });
    return entries
      .filter(entry => entry.isDirectory() && !entry.name.startsWith('.'))
      .map(entry => entry.name);
  } catch {
    return [];
  }
}

async function main() {
  const repoRoot = process.cwd();
  const statusesPath = path.join(repoRoot, 'modules_statuses.json');
  const { modules: requestedModules } = parseArgs();

  // Read module statuses
  let statuses;
  try {
    const content = await fs.readFile(statusesPath, 'utf8');
    statuses = JSON.parse(content);
  } catch (err) {
    console.warn(`modules_statuses.json not found at ${statusesPath}: ${err.message}`);
    statuses = {};
  }

  // Get all available modules
  const availableModules = await getAvailableModules(repoRoot);

  // Determine which modules to build
  let modulesToBuild = [];

  if (requestedModules === null) {
    // Build all enabled modules
    modulesToBuild = Object.entries(statuses)
      .filter(([, enabled]) => enabled)
      .map(([name]) => name);
    console.log('Building all enabled modules...');
  } else {
    // Build only requested modules (regardless of enabled status)
    modulesToBuild = requestedModules;
    console.log(`Building requested modules: ${requestedModules.join(', ')}`);
  }

  if (modulesToBuild.length === 0) {
    console.log('No modules to build.');
    return;
  }

  let successCount = 0;
  let skipCount = 0;
  let failCount = 0;

  for (const moduleName of modulesToBuild) {
    // Validate module name to prevent command injection
    if (!isValidModuleName(moduleName)) {
      console.error(`\n[SKIP] Invalid module name '${moduleName}': contains disallowed characters`);
      skipCount++;
      continue;
    }

    // Check if module exists
    const moduleNameLower = moduleName.toLowerCase();
    const actualModuleName = availableModules.find(m => m.toLowerCase() === moduleNameLower);

    if (!actualModuleName) {
      console.warn(`\n[SKIP] Module '${moduleName}' not found in modules directory`);
      skipCount++;
      continue;
    }

    const viteConfigPath = path.join(repoRoot, 'modules', actualModuleName, 'vite.config.js');

    try {
      await fs.access(viteConfigPath);
    } catch {
      console.warn(`\n[SKIP] Module '${actualModuleName}': vite.config.js not found`);
      skipCount++;
      continue;
    }

    console.log(`\n=== Building module: ${actualModuleName} ===`);
    try {
      // Run vite build for each module using execFileSync to avoid shell injection
      execFileSync('npx', ['vite', 'build', '--config', path.relative(repoRoot, viteConfigPath)], {
        stdio: 'inherit',
        cwd: repoRoot,
      });
      successCount++;
    } catch (err) {
      console.error(`[FAIL] Build failed for module '${actualModuleName}': ${err.message}`);
      failCount++;
    }
  }

  console.log('\n========================================');
  console.log(`Build Summary: ${successCount} succeeded, ${skipCount} skipped, ${failCount} failed`);

  if (failCount > 0) {
    process.exit(1);
  }
}

main();
