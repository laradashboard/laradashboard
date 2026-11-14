// Quality gate: renders key frames of every scene into a contact sheet and audits motion.
// Usage: node scripts/qa.mjs <project> [--per=3]
// Output: projects/<name>/qa/sheet.jpg (+ individual frames) and a printed report.
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { renderStill } from '@remotion/renderer';
import { CHROME, bundleProject, projectDir, resolveStoryboard, selectProduct } from './lib.mjs';

const args = process.argv.slice(2);
const dir = projectDir(args.find((a) => !a.startsWith('--')));
const per = +(args.find((a) => a.startsWith('--per='))?.split('=')[1] ?? 3);
const sb = resolveStoryboard(dir);
const serveUrl = await bundleProject(dir);
const composition = await selectProduct(serveUrl, sb);
const fps = composition.fps;

// Rebuild the timeline the same way src/Video.tsx does (keep in sync with timeline()).
const tr = Math.round((sb.transitionDuration ?? 0.6) * fps);
let at = 0;
const items = sb.scenes.map((s, i) => {
  const base = s.type === 'screen' ? ((s.trim?.[1] ?? s._events.duration) - (s.trim?.[0] ?? 0)) / (s.rate ?? 1) : s.duration;
  const frames = Math.round((s.voDuration ? Math.max(base, s.voDuration + 0.9) : base) * fps);
  const last = i === sb.scenes.length - 1;
  const trF = last || (s.transition ?? sb.transition) === 'none' ? 0 : Math.min(tr, Math.floor(frames / 2));
  const it = { s, i, from: at, frames, trF };
  at += frames - trF;
  return it;
});

const out = path.join(dir, 'qa');
fs.rmSync(out, { recursive: true, force: true });
fs.mkdirSync(out, { recursive: true });
const files = [];
const warnings = [];
for (const it of items) {
  const lo = it.from + (items[it.i - 1]?.trF ?? 0) + 6, hi = it.from + it.frames - it.trF - 4;
  for (let k = 0; k < per; k++) {
    const frame = Math.round(per === 1 ? (lo + hi) / 2 : lo + ((hi - lo) * k) / (per - 1));
    const file = path.join(out, `${String(it.i).padStart(2, '0')}-${k}-f${frame}.jpg`);
    await renderStill({ serveUrl, composition, inputProps: sb, frame, output: file, imageFormat: 'jpeg', jpegQuality: 80, scale: 0.5, browserExecutable: CHROME, chromeMode: 'chrome-for-testing', timeoutInMilliseconds: 180000 });
    files.push(file);
  }
  const s = it.s, secs = it.frames / fps;
  if (s.type === 'screen') {
    const ev = s._events?.events ?? [];
    const moves = s.camera?.length ?? ev.filter((e) => ['click', 'focus', 'type', 'highlight'].includes(e.type)).length;
    if (s.cameraMode !== 'static' && moves === 0 && !s.headline && secs > 3) warnings.push(`scene ${it.i} (${s.chapter ?? s.src}): ${secs.toFixed(1)}s screen shot with no camera moves - add focus/click events or camera keys`);
    if (secs > 9 && moves < 2) warnings.push(`scene ${it.i}: ${secs.toFixed(1)}s long with ${moves} camera move(s) - split it or add beats`);
  } else if (secs > 7) warnings.push(`scene ${it.i} (${s.type}): ${secs.toFixed(1)}s card - keep text cards under ~6s`);
  if (s.voDuration && s.type !== 'screen' && s.voDuration + 0.9 > s.duration) warnings.push(`scene ${it.i}: voice (${s.voDuration.toFixed(1)}s) stretched the card beyond its duration`);
}
const cols = per;
execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-pattern_type', 'glob', '-i', path.join(out, '*.jpg'), '-vf', `scale=640:-1,tile=${cols}x${items.length}:padding=6:color=white`, '-frames:v', '1', path.join(out, 'sheet.jpg')]);
console.log(`Scenes: ${items.length}   Total: ${(composition.durationInFrames / fps).toFixed(1)}s`);
items.forEach((it) => console.log(`  ${String(it.i).padStart(2)}  ${it.s.type.padEnd(9)} ${(it.from / fps).toFixed(1).padStart(5)}s  ${(it.frames / fps).toFixed(1)}s  ${it.s.chapter ?? it.s.title ?? it.s.text ?? ''}`));
console.log(warnings.length ? `\nWarnings:\n - ${warnings.join('\n - ')}` : '\nNo motion warnings.');
console.log(`\nContact sheet: ${path.join(out, 'sheet.jpg')}`);
