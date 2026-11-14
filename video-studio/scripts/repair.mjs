// Finds one/two-frame render glitches (a frame captured before the page painted: white or
// partially blank) and patches them with a freshly rendered still of the same frame.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { renderStill } from '@remotion/renderer';
import { CHROME, frameStats } from './lib.mjs';

/** Per-frame mean brightness. */
export async function frameBrightness(file) { return (await frameStats(file)).y; }

/** One frame as a 320px-wide grayscale buffer (video frame index n, or an image file). */
function grayFrame(file, n) {
  const sel = n === undefined ? [] : ['-vf', `select=eq(n\\,${n}),scale=320:180,format=gray`, '-vsync', '0'];
  const vf = n === undefined ? ['-vf', 'scale=320:180,format=gray'] : sel;
  return execFileSync('ffmpeg', ['-loglevel', 'error', '-i', file, ...vf, '-frames:v', '1', '-f', 'rawvideo', '-'], { maxBuffer: 1 << 24 });
}
const mad = (a, b, c) => { let s = 0; for (let i = 0; i < a.length; i++) s += Math.abs(a[i] - (b[i] + c[i]) / 2); return s / a.length; };

/** Frames whose brightness jumps away from neighbours that agree with each other. */
export function findGlitches(y, d = []) {
  const bad = new Set();
  const T = 1.2, SAME = 0.6;
  for (let i = 1; i < y.length - 1; i++) {
    if (Math.abs(y[i] - y[i - 1]) > T && Math.abs(y[i] - y[i + 1]) > T && Math.abs(y[i - 1] - y[i + 1]) < SAME) bad.add(i);
    if (i < y.length - 2 && Math.abs(y[i] - y[i + 1]) < SAME && Math.abs(y[i] - y[i - 1]) > T && Math.abs(y[i + 1] - y[i + 2]) > T && Math.abs(y[i - 1] - y[i + 2]) < SAME) { bad.add(i); bad.add(i + 1); }
  }
  // Flash: a frame much brighter/darker than BOTH neighbours (e.g. a white frame mid-transition).
  for (let i = 1; i < y.length - 1; i++) {
    if (y[i] - Math.max(y[i - 1], y[i + 1]) > 3 || Math.min(y[i - 1], y[i + 1]) - y[i] > 3) bad.add(i);
  }
  // Corrupt frame (tiled / partial render): pixels jump away and straight back, brightness may not change.
  for (let i = 2; i < d.length - 1; i++) {
    const calm = Math.max(d[i - 2], d[i + 1]);
    if (d[i - 1] > 4 * calm + 2 && d[i] > 4 * calm + 2) bad.add(i);
  }
  return [...bad].sort((a, b) => a - b);
}

export async function repairGlitches({ file, serveUrl, composition, inputProps, from = 0, crf = 16 }) {
  for (let pass = 1; pass <= 2; pass++) {
    const st = await frameStats(file);
    const glitches = findGlitches(st.y, st.diff);
    if (!glitches.length) { console.log(pass === 1 ? '✓ no render glitches' : '✓ glitches repaired'); return; }
    console.log(`… repairing ${glitches.length} glitch frame(s): ${glitches.map((i) => i + from).join(', ')}`);
    const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'repair-'));
    const stills = [];
    for (const i of glitches) {
      const out = path.join(dir, `${i}.png`);
      await renderStill({ serveUrl, composition, inputProps, frame: i + from, output: out, imageFormat: 'png', browserExecutable: CHROME, chromeMode: 'chrome-for-testing', timeoutInMilliseconds: 180000 });
      // Only patch when the fresh still matches its neighbours better than the rendered frame did.
      const prev = grayFrame(file, Math.max(0, i - 1)), next = grayFrame(file, i + 1);
      if (mad(grayFrame(out), prev, next) < mad(grayFrame(file, i), prev, next)) stills.push({ i, out });
    }
    if (!stills.length) { console.log('✓ no patch needed (flagged frames were genuine)'); fs.rmSync(dir, { recursive: true, force: true }); return; }
    const inputs = ['-i', file, ...stills.flatMap((s) => ['-i', s.out])];
    let chain = '[0:v]', filter = '';
    stills.forEach((s, k) => { const o = `v${k}`; filter += `${chain}[${k + 1}:v]overlay=0:0:enable='eq(n\\,${s.i})'[${o}];`; chain = `[${o}]`; });
    const fixed = file.replace(/\.mp4$/, '.fixed.mp4');
    execFileSync('ffmpeg', ['-y', '-loglevel', 'error', ...inputs, '-filter_complex', filter.slice(0, -1).replace(/\[v\d+\]$/, '[vout]'), '-map', '[vout]', '-map', '0:a?', '-c:v', 'libx264', '-crf', String(crf), '-preset', 'slow', '-pix_fmt', 'yuv420p', '-c:a', 'copy', fixed]);
    fs.renameSync(fixed, file);
    fs.rmSync(dir, { recursive: true, force: true });
  }
  const st = await frameStats(file);
  const left = findGlitches(st.y, st.diff);
  if (left.length) console.warn(`! ${left.length} glitch frame(s) remain: ${left.join(', ')}`);
}
