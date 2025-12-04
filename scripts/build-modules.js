import fs from 'fs/promises';
import path from 'path';
import { execSync } from 'child_process';

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

    const viteConfigPath = path.join(repoRoot, 'modules', moduleName, 'vite.config.js');
    try {
      await fs.access(viteConfigPath);
    } catch (err) {
      console.warn(`Skipping module '${moduleName}': vite.config.js not found at ${viteConfigPath}`);
      continue;
    }

    console.log(`\n=== Building module: ${moduleName} ===`);
    try {
      // Run vite build for each module. Use npx so the local installation is used.
      execSync(`npx vite build --config ${path.relative(repoRoot, viteConfigPath)}`, {
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
