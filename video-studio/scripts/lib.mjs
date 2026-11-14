// Shared helpers for render / qa scripts.
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';

export const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
export const CHROME = process.env.REMOTION_BROWSER ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

export function projectDir(arg) {
  if (!arg) throw new Error('Usage: <script> <project-name-or-dir>');
  const dir = fs.existsSync(arg) ? path.resolve(arg) : path.join(ROOT, 'projects', arg);
  if (!fs.existsSync(path.join(dir, 'storyboard.json'))) throw new Error(`No storyboard.json in ${dir}`);
  return dir;
}

export function probeDuration(file) {
  return parseFloat(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', file]).toString());
}

/** Reads storyboard.json and inlines each screen scene's events file (as `_events`). */
export function resolveStoryboard(dir) {
  const sb = JSON.parse(fs.readFileSync(path.join(dir, 'storyboard.json'), 'utf8'));
  for (const s of sb.scenes) {
    if (s.type !== 'screen') continue;
    const evFile = s.events ?? s.src.replace(/\.mp4$/, '.events.json');
    const abs = path.join(dir, evFile);
    if (fs.existsSync(abs)) s._events = JSON.parse(fs.readFileSync(abs, 'utf8'));
    else s._events = { size: s.size, duration: probeDuration(path.join(dir, s.src)), events: [] };
    if (!s.size) s.size = s._events.size;
  }
  // Title hero windows use still frames (extracted once): moving video there is heavy and its
  // frame cache can mix up clips that share a timestamp.
  for (const s of sb.scenes) for (const h of s.hero ?? []) {
    if (!/\.(mp4|mov|webm)$/i.test(h.src)) continue;
    const rel = `clips/.stills/${path.basename(h.src).replace(/\.\w+$/, '')}-${h.at ?? 0}.jpg`;
    const abs = path.join(dir, rel);
    if (!fs.existsSync(abs)) {
      fs.mkdirSync(path.dirname(abs), { recursive: true });
      execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-ss', String(h.at ?? 0), '-i', path.join(dir, h.src), '-frames:v', '1', '-q:v', '2', abs]);
    }
    h.src = rel;
  }
  // "@library/<file>" -> shared track in assets/music, copied into the project (Remotion serves the project folder).
  if (typeof sb.music === 'string' && sb.music.startsWith('@library/')) {
    const name = sb.music.slice('@library/'.length);
    const src = path.join(ROOT, 'assets', 'music', name);
    if (!fs.existsSync(src)) throw new Error(`Music library has no ${name} (see assets/music/LICENSES.md)`);
    const rel = `.library/${name.replace(/\.\w+$/, '')}-norm.mp3`;
    fs.mkdirSync(path.join(dir, '.library'), { recursive: true });
    if (!fs.existsSync(path.join(dir, rel))) normalizeLoudness(src, path.join(dir, rel));
    sb.music = rel;
  }
  for (const k of ['music']) if (sb[k] && !fs.existsSync(path.join(dir, sb[k]))) { console.warn(`! ${k} file missing (${sb[k]}), skipping`); delete sb[k]; }
  for (const s of sb.scenes) if (s.voFile && !fs.existsSync(path.join(dir, s.voFile))) { delete s.voFile; delete s.voDuration; }
  return sb;
}

export async function bundleProject(dir) {
  const { bundle } = await import('@remotion/bundler');
  return bundle({ entryPoint: path.join(ROOT, 'src/index.ts'), publicDir: dir, onProgress: () => {} });
}

export async function selectProduct(serveUrl, inputProps) {
  const { selectComposition } = await import('@remotion/renderer');
  return selectComposition({ serveUrl, id: 'Product', inputProps, browserExecutable: CHROME, chromeMode: 'chrome-for-testing' });
}

/**
 * Streams a video as small grayscale frames and returns per-frame stats:
 *   y[i]    mean brightness of frame i (0-255)
 *   diff[i] mean absolute difference between frame i and i+1 (0-255)
 */
export async function frameStats(file, width = 320) {
  const { spawn } = await import('node:child_process');
  const [w0, h0] = execFileSync('ffprobe', ['-v', 'error', '-select_streams', 'v:0', '-show_entries', 'stream=width,height', '-of', 'csv=p=0', file]).toString().trim().split(',').map(Number);
  const w = width, h = Math.round((h0 / w0) * width / 2) * 2, size = w * h;
  const ff = spawn('ffmpeg', ['-loglevel', 'error', '-i', file, '-vf', `scale=${w}:${h},format=gray`, '-f', 'rawvideo', '-']);
  const y = [], diff = [];
  let prev = null, buf = Buffer.alloc(0);
  for await (const chunk of ff.stdout) {
    buf = Buffer.concat([buf, chunk]);
    while (buf.length >= size) {
      const fr = buf.subarray(0, size);
      let s = 0, d = 0;
      for (let i = 0; i < size; i++) { s += fr[i]; if (prev) d += Math.abs(fr[i] - prev[i]); }
      y.push(s / size);
      if (prev) diff.push(d / size);
      prev = Buffer.from(fr);
      buf = buf.subarray(size);
    }
  }
  return { y, diff };
}

/** Loudness-normalises audio to -16 LUFS (voice level). Music then plays at musicVolume (~-22 LUFS) and ducks under voice. */
export function normalizeLoudness(src, dest, lufs = -16) {
  execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-i', src, '-af', `loudnorm=I=${lufs}:TP=-1.5:LRA=11`, '-ar', '44100', '-c:a', 'libmp3lame', '-b:a', '256k', dest]);
}
