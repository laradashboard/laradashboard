// Brings a user's own screen recording into a project.
// Usage:
//   node scripts/ingest.mjs <project> <recording.mov|mp4> [--name=raw] [--width=1440]
//     -> clips/<name>.mp4 (H.264, 30fps, CRF 16), and qa/ingest-<name>/ thumbnails every 0.5s
//        with a timestamped contact sheet, so the editor can see where clicks/moments happen.
//   node scripts/ingest.mjs <project> --marks=marks.txt [--name=raw]
//     -> converts a marks file into clips/<name>.events.json for the auto-camera.
//
// marks.txt format (one per line, coordinates in the --width coordinate space, # comments allowed):
//   12.4 click 830 410 [w h] [label...]     zoom on a click (optional element size w h)
//   15.0 focus 300 200 600 300 [hold] [zoom] camera focus on a region
//   18.2 highlight 900 120 160 44 2.0 [label...]  spotlight a region for 2.0s
//   21.0 scroll 1.5                          zoom out during a 1.5s scroll
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { probeDuration } from './lib.mjs';
import { ROOT } from './lib.mjs';

const args = process.argv.slice(2);
const flag = (n, d) => args.find((a) => a.startsWith(`--${n}=`))?.split('=').slice(1).join('=') ?? d;
const [projectArg, input] = args.filter((a) => !a.startsWith('--'));
const dir = fs.existsSync(projectArg ?? '') ? path.resolve(projectArg) : path.join(ROOT, 'projects', projectArg ?? '');
fs.mkdirSync(path.join(dir, 'clips'), { recursive: true });
const name = flag('name', 'raw');
const width = +flag('width', 1440);

const marks = flag('marks');
if (marks) {
  const clip = path.join(dir, 'clips', `${name}.mp4`);
  const [pw, ph] = execFileSync('ffprobe', ['-v', 'error', '-select_streams', 'v:0', '-show_entries', 'stream=width,height', '-of', 'csv=p=0', clip]).toString().trim().split(',').map(Number);
  const size = [width, Math.round((ph / pw) * width)];
  const events = [];
  let cursor = null;
  for (const raw of fs.readFileSync(path.resolve(dir, marks), 'utf8').split('\n')) {
    const line = raw.replace(/#.*/, '').trim();
    if (!line) continue;
    const [t, type, ...r] = line.split(/\s+/);
    const n = (i) => +r[i];
    const T = +t;
    if (type === 'click') {
      const x = n(0), y = n(1), hasBox = r.length >= 4 && !isNaN(n(2)) && !isNaN(n(3));
      const box = hasBox ? { x: x - n(2) / 2, y: y - n(3) / 2, w: n(2), h: n(3) } : undefined;
      // Synthetic cursor glides from the previous click to this one.
      if (cursor) events.push({ type: 'move', t0: Math.max(cursor.t, T - 0.9), t1: T - 0.15, x0: cursor.x, y0: cursor.y, x, y });
      events.push({ type: 'click', t: T, x, y, ...(box ? { box } : {}), ...(r.slice(hasBox ? 4 : 2).length ? { label: r.slice(hasBox ? 4 : 2).join(' ') } : {}) });
      cursor = { t: T, x, y };
    } else if (type === 'focus') events.push({ type: 'focus', t: T, box: { x: n(0), y: n(1), w: n(2), h: n(3) }, ...(r[4] ? { hold: n(4) } : {}), ...(r[5] ? { zoom: n(5) } : {}) });
    else if (type === 'highlight') events.push({ type: 'highlight', t: T, box: { x: n(0), y: n(1), w: n(2), h: n(3) }, dur: n(4) || 1.8, ...(r[5] ? { label: r.slice(5).join(' ') } : {}) });
    else if (type === 'scroll') events.push({ type: 'scroll', t0: T, t1: T + (n(0) || 1.2), dy: 0 });
    else console.warn(`? unknown mark: ${line}`);
  }
  const out = path.join(dir, 'clips', `${name}.events.json`);
  fs.writeFileSync(out, JSON.stringify({ size, duration: +probeDuration(clip).toFixed(2), events }, null, 1));
  console.log(`✓ ${events.length} events -> ${out}  (coordinate space ${size.join('x')})\n  Use "size": [${size}] and "cursor": false (the recording already shows the real cursor) in the storyboard.`);
  process.exit(0);
}

if (!input) { console.error('Usage: node scripts/ingest.mjs <project> <recording> | --marks=marks.txt'); process.exit(1); }
const clip = path.join(dir, 'clips', `${name}.mp4`);
execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-i', path.resolve(input), '-vf', 'fps=30,format=yuv420p', '-c:v', 'libx264', '-crf', '16', '-preset', 'medium', '-an', clip]);
const dur = probeDuration(clip);
const thumbs = path.join(dir, 'qa', `ingest-${name}`);
fs.mkdirSync(thumbs, { recursive: true });
execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-i', clip, '-vf', `fps=2,scale=${width}:-2,drawtext=text='%{pts\\:hms}':x=20:y=20:fontsize=36:fontcolor=white:box=1:boxcolor=black@0.6:boxborderw=8`, path.join(thumbs, '%05d.jpg')]);
const n = fs.readdirSync(thumbs).filter((f) => /^\d+\.jpg$/.test(f)).length;
for (let i = 0; i * 24 < n; i++) {
  execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-start_number', String(i * 24 + 1), '-i', path.join(thumbs, '%05d.jpg'), '-frames:v', '1', '-vf', 'scale=480:-2,tile=6x4:padding=4', path.join(thumbs, `sheet-${String(i).padStart(2, '0')}.jpg`)]);
}
console.log(`✓ ${clip}  ${dur.toFixed(1)}s\n  Thumbnails (every 0.5s, ${width}px wide, timestamped): ${thumbs}\n  Next: write marks.txt from the sheets, then run with --marks=marks.txt --name=${name}`);
