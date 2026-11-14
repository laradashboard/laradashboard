// Downloads the shared music library listed in assets/music/tracks.json (skips files already present).
// The tracks are not committed: their licence forbids redistributing them as standalone files.
// Usage: npm run music
import fs from 'node:fs';
import path from 'node:path';
import { ROOT } from './lib.mjs';

const dir = path.join(ROOT, 'assets', 'music');
const tracks = JSON.parse(fs.readFileSync(path.join(dir, 'tracks.json'), 'utf8'));
for (const t of tracks) {
  const dest = path.join(dir, t.file);
  if (fs.existsSync(dest)) { console.log(`= ${t.file}`); continue; }
  const res = await fetch(t.url, { headers: { Referer: t.page, 'User-Agent': 'Mozilla/5.0' } });
  if (!res.ok) { console.error(`✗ ${t.file}: HTTP ${res.status} - download it manually from ${t.page}`); continue; }
  fs.writeFileSync(dest, Buffer.from(await res.arrayBuffer()));
  console.log(`✓ ${t.file}  (${t.title} by ${t.author}, ${t.licence})`);
}
