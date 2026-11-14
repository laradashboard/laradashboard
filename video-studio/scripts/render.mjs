// Renders a project to out/<name>.mp4.
// Usage: node scripts/render.mjs <project> [--preview] [--from=SEC --to=SEC]
//   --preview  half resolution, faster encode (for quick checks)
import fs from 'node:fs';
import path from 'node:path';
import { renderMedia } from '@remotion/renderer';
import { CHROME, ROOT, bundleProject, projectDir, resolveStoryboard, selectProduct } from './lib.mjs';
import { repairGlitches } from './repair.mjs';

const args = process.argv.slice(2);
const dir = projectDir(args.find((a) => !a.startsWith('--')));
const flag = (n) => args.find((a) => a.startsWith(`--${n}=`))?.split('=')[1];
const preview = args.includes('--preview');
const name = path.basename(dir);

const inputProps = resolveStoryboard(dir);
const serveUrl = await bundleProject(dir);
const composition = await selectProduct(serveUrl, inputProps);
const fps = composition.fps;
const from = flag('from') ? Math.round(+flag('from') * fps) : 0;
const to = flag('to') ? Math.min(composition.durationInFrames - 1, Math.round(+flag('to') * fps)) : composition.durationInFrames - 1;

fs.mkdirSync(path.join(ROOT, 'out'), { recursive: true });
const outputLocation = path.join(ROOT, 'out', `${name}${preview ? '-preview' : ''}.mp4`);
let last = -1;
try {
await renderMedia({
  serveUrl, composition, inputProps, outputLocation, codec: 'h264',
  crf: preview ? 23 : 16, scale: preview ? 0.5 : 1, frameRange: [from, to],
  imageFormat: 'jpeg', jpegQuality: preview ? 80 : 92, x264Preset: preview ? 'veryfast' : 'slow',
  browserExecutable: CHROME, chromeMode: 'chrome-for-testing', audioBitrate: '192k',
  timeoutInMilliseconds: 180000, concurrency: process.env.CONCURRENCY ? +process.env.CONCURRENCY : 4,
  offthreadVideoCacheSizeInBytes: 2 * 1024 ** 3,
  onProgress: ({ progress }) => { const p = Math.floor(progress * 20); if (p !== last) { last = p; process.stdout.write(`\r${'█'.repeat(p)}${'░'.repeat(20 - p)} ${Math.round(progress * 100)}%`); } },
});
} catch (e) { console.error(`\n✗ Render failed: ${e.message?.split('\n')[0]}${e.frame !== undefined ? ` (frame ${e.frame})` : ''}`); process.exit(1); }
console.log('');
if (!preview && !args.includes('--no-repair')) await repairGlitches({ file: outputLocation, serveUrl, composition, inputProps, from });
console.log(`✓ ${outputLocation}  (${((to - from + 1) / fps).toFixed(1)}s)`);
