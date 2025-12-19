import fs from 'fs/promises';
import path from 'path';
import { execFileSync } from 'child_process';

// Validate module name to prevent shell injection
function isValidModuleName(name) {
  // Only allow alphanumeric, hyphens, underscores (no shell metacharacters)
  return typeof name === 'string' && /^[a-zA-Z0-9_-]+$/.test(name);
}

async function main() {
  const repoRoot = process.cwd();
  const statusesPath = path.join(repoRoot, 'modules_statuses.json');

  let content;
  try {
    content = await fs.readFile(statusesPath, 'utf8');
  } catch (err) {
    console.error(`modules_statuses.json not found at ${statusesPath}: ${err.message}`);
    // Nothing to build
    return;
  }

  let statuses;
  try {
    statuses = JSON.parse(content);
  } catch (err) {
    console.error(`Invalid JSON in modules_statuses.json: ${err.message}`);
    process.exit(1);
  }

  for (const [moduleName, enabled] of Object.entries(statuses)) {
    if (!enabled) continue;

    // Validate module name to prevent command injection
    if (!isValidModuleName(moduleName)) {
      console.error(`Invalid module name '${moduleName}': contains disallowed characters`);
      process.exit(1);
    }

    const viteConfigPath = path.join(repoRoot, 'modules', moduleName, 'vite.config.js');
    try {
      await fs.access(viteConfigPath);
    } catch (err) {
      console.warn(`Skipping module '${moduleName}': vite.config.js not found at ${viteConfigPath}`);
      continue;
    }

    console.log(`\n=== Building module: ${moduleName} ===`);
    try {
      // Run vite build for each module using execFileSync to avoid shell injection
      execFileSync('npx', ['vite', 'build', '--config', path.relative(repoRoot, viteConfigPath)], {
        stdio: 'inherit',
        cwd: repoRoot,
      });
    } catch (err) {
      console.error(`Build failed for module '${moduleName}': ${err.message}`);
      process.exit(1);
    }
  }

  console.log('\nAll enabled modules built.');
}

main();
