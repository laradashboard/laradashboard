// Smoothness check for a rendered video: finds flicker, stutter and jolts by measuring
// how much each frame differs from the previous one (exact mean grayscale difference, 320px).
// Usage: node scripts/smoothness.mjs <video.mp4 | project>     (project -> out/<project>.mp4)
// Exit code 1 if problems were found.
//   flicker  - one frame differs a lot more than its neighbours (something pops in/out for a frame)
//   stutter  - during motion, frames alternate moving/frozen (dropped or duplicated source frames)
//   jolt     - motion starts or stops at full speed within one frame (un-eased transition/move)
// Note: instant UI changes inside the footage (a tab switch, a toast) also count; judge those by eye.
import fs from 'node:fs';
import path from 'node:path';
import { ROOT, frameStats } from './lib.mjs';

const arg = process.argv[2];
if (!arg) { console.error('Usage: node scripts/smoothness.mjs <video.mp4 | project>'); process.exit(2); }
const file = fs.existsSync(arg) ? path.resolve(arg) : path.join(ROOT, 'out', `${arg}.mp4`);
const fps = 30;
const { diff: d } = await frameStats(file); // d[i] = mean abs difference between frame i and i+1 (0-255)
const ts = (i) => `${Math.floor((i + 1) / fps / 60)}:${((i + 1) / fps % 60).toFixed(2).padStart(5, '0')}`;

const flicker = [], stutter = [], jolt = [];
for (let i = 2; i < d.length - 2; i++) {
  const nb = (d[i - 2] + d[i - 1] + d[i + 1] + d[i + 2]) / 4;
  if (d[i] > 1 && d[i] > 3 * nb + 0.4) flicker.push(i);
  // Frozen frame in the middle of motion: both sides move, this one doesn't.
  if (d[i - 1] > 0.4 && d[i + 1] > 0.4 && d[i] < 0.25 * Math.min(d[i - 1], d[i + 1])) stutter.push(i);
  if ((d[i - 1] < 0.15 && d[i] > 2 && d[i + 1] > 1.5) || (d[i + 1] < 0.15 && d[i] > 2 && d[i - 1] > 1.5)) jolt.push(i);
}
// Group stutter frames that are close together into ranges.
const group = (arr, gap = 8) => arr.reduce((g, i) => { const last = g[g.length - 1]; if (last && i - last[1] <= gap) last[1] = i; else g.push([i, i]); return g; }, []);
const stutterRanges = group(stutter).filter(([a, b]) => stutter.filter((i) => i >= a && i <= b).length >= 3);

console.log(`${path.basename(file)}: ${d.length} frames (${(d.length / fps).toFixed(1)}s)`);
const report = (name, items, fmt) => console.log(items.length ? `✗ ${name}: ${items.length}\n${items.map(fmt).join('\n')}` : `✓ no ${name}`);
report('flicker', flicker, (i) => `   ${ts(i)}  diff ${d[i].toFixed(1)}`);
report('stutter', stutterRanges, ([a, b]) => `   ${ts(a)}–${ts(b)}  ${stutter.filter((i) => i >= a && i <= b).length} frozen frames during motion`);
report('jolts', jolt, (i) => `   ${ts(i)}  motion ${d[i - 1] < 0.15 ? 'starts' : 'stops'} abruptly`);
process.exit(flicker.length + stutterRanges.length + jolt.length ? 1 : 0);
