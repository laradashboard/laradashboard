// Records app screen clips + an interaction log that drives the camera, cursor and highlights.
// Usage: node scripts/record.mjs <project> [sceneName ...]
//
// The project needs a `scenes.mjs`:
//   export const config = { baseUrl, viewport: { width: 1440, height: 900 }, scale: 2, login: async (page, baseUrl) => {...} };
//   export const scenes = { async deals(r) { await r.goto('/admin/crm/deals'); await r.click('text=Website Redesign'); ... } };
//
// Output per scene: clips/<name>.mp4 (no cursor burned in) and clips/<name>.events.json.
// Recorder API (r): goto, move, click, hover, focus, highlight, type, scroll, wait, drag, page, log.
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';
import { projectDir } from './lib.mjs';

const [projectArg, ...only] = process.argv.slice(2);
const dir = projectDir(projectArg);
const { config, scenes } = await import(path.join(dir, 'scenes.mjs'));
const W = config.viewport?.width ?? 1440, H = config.viewport?.height ?? 900, DPR = config.scale ?? 2;
const BASE = process.env.APP_URL ?? config.baseUrl ?? 'http://localhost:8000';
const OUT = path.join(dir, 'clips');
fs.mkdirSync(OUT, { recursive: true });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
// Virtual clock: equals wall time, except stepped scrolls advance it by exactly 1/30s per captured frame.
let clock = { offset: 0, stepping: false, waiter: null, frames: null };
const now = () => Date.now() / 1000 - clock.offset;
const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);

// Hide dev-only noise during capture.
const INIT = () => {
  const css = '.phpdebugbar,#phpdebugbar,[class*="toast"],#__vite-error-overlay{display:none!important}html{scroll-behavior:auto!important}';
  const add = () => { const s = document.createElement('style'); s.textContent = css; document.head.appendChild(s); };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', add); else add();
};

function recorder(page) {
  const events = [];
  let cur = { x: W * 0.62, y: H * 0.3 };
  let started = false;
  const log = (e) => { if (started) events.push(e); };
  const box = async (target) => {
    // Accepts a selector string, a Playwright locator, a point {x,y} or a region {x,y,w,h} (CSS px).
    if (target && typeof target === 'object' && 'x' in target && 'y' in target && !('boundingBox' in target)) return 'w' in target ? target : { x: target.x - 1, y: target.y - 1, w: 2, h: 2 };
    const loc = typeof target === 'string' ? page.locator(target).first() : target;
    await loc.scrollIntoViewIfNeeded().catch(() => {});
    const b = await loc.boundingBox();
    if (!b) throw new Error(`No element for ${target}`);
    return { x: Math.round(b.x), y: Math.round(b.y), w: Math.round(b.width), h: Math.round(b.height) };
  };
  const center = (b) => ({ x: b.x + b.w / 2, y: b.y + b.h / 2 });
  /**
   * Deterministic capture: runs apply(k/N) for k=1..N, waits for paint and screenshots each step on a
   * fixed 30fps virtual clock. Perfectly smooth however slow the page or capture is.
   */
  const stepFrames = async (ms, apply) => {
    const t0 = now();
    const N = Math.max(2, Math.round((ms / 1000) * 30));
    clock.stepping = true;
    try {
      for (let k = 1; k <= N; k++) {
        await apply(k / N);
        await page.evaluate(() => new Promise((res) => requestAnimationFrame(() => requestAnimationFrame(res))));
        const { data } = await clock.cdp.send('Page.captureScreenshot', { format: 'jpeg', quality: config.quality ?? 85 });
        clock.push(data, t0 + k / 30);
      }
    } finally {
      clock.stepping = false;
      clock.offset = Date.now() / 1000 - (t0 + N / 30); // resume real time from the virtual end
    }
    return { t0, t1: t0 + N / 30 };
  };
  const settle = async () => {
    await page.waitForLoadState('networkidle', { timeout: 6000 }).catch(() => {});
    await page.evaluate(() => document.fonts.ready);
    await sleep(500);
  };
  const r = {
    page, events, log,
    get started() { return started; },
    start() { if (!started) { started = true; r.t0 = now(); } },
    async goto(url, { wait } = {}) {
      if (started) log({ type: 'nav', t: now(), url });
      await page.goto(url.startsWith('http') ? url : BASE + url, { waitUntil: 'domcontentloaded' });
      if (wait) await page.locator(wait).first().waitFor({ timeout: 30000 }).catch(() => {});
      await settle();
      r.start();
    },
    /** Moves the (virtual) mouse to an element or {x,y}, eased, over `ms`. */
    async move(target, { ms = 750, stepped = false } = {}) {
      const b = await box(target), p = center(b);
      const from = { ...cur };
      const at = (e) => page.mouse.move(from.x + (p.x - from.x) * e, from.y + (p.y - from.y) * e);
      let t0, t1;
      if (stepped) ({ t0, t1 } = await stepFrames(ms, (k) => at(ease(k)))); // use when the page reacts to the mouse (drags)
      else {
        t0 = now();
        const steps = Math.max(8, Math.round(ms / 30));
        for (let i = 1; i <= steps; i++) { await at(ease(i / steps)); await sleep(ms / steps); }
        t1 = now();
      }
      cur = p;
      log({ type: 'move', t0, t1, x0: from.x, y0: from.y, x: p.x, y: p.y });
      return b;
    },
    /** Moves to and clicks an element. zoom: number | false (false = don't zoom the camera). */
    async click(target, { ms, zoom, label, pause = 350 } = {}) {
      const b = await r.move(target, { ms });
      await sleep(180);
      log({ type: 'click', t: now(), x: cur.x, y: cur.y, box: b, ...(zoom !== undefined ? { zoom } : {}), ...(label ? { label } : {}) });
      await page.mouse.down(); await sleep(70); await page.mouse.up();
      await sleep(pause);
      return b;
    },
    /** Moves to an element and asks the camera to zoom on it without clicking. */
    async hover(target, { ms, zoom, hold = 1.2 } = {}) {
      const b = await r.move(target, { ms });
      log({ type: 'focus', t: now() - 0.2, box: b, hold, ...(zoom ? { zoom } : {}) });
      await sleep(hold * 1000);
      return b;
    },
    /** Camera focus on a region without moving the mouse. */
    async focus(target, { zoom, hold = 1.6, label } = {}) {
      const b = await box(target);
      log({ type: 'focus', t: now(), box: b, hold, ...(zoom ? { zoom } : {}), ...(label ? { label } : {}) });
      await sleep(hold * 1000);
      return b;
    },
    /** Spotlight + outline around an element (optional label under it). */
    async highlight(target, { ms = 1800, label } = {}) {
      const b = await box(target);
      log({ type: 'highlight', t: now(), dur: ms / 1000, box: b, ...(label ? { label } : {}) });
      await sleep(ms);
      return b;
    },
    async type(target, text, { delay = 55 } = {}) {
      const b = await r.click(target, { zoom: false, pause: 150 });
      const t0 = now();
      await page.keyboard.type(text, { delay });
      log({ type: 'type', t0, t1: now(), box: b });
      await sleep(400);
    },
    /**
     * Eased scroll of the scrollable panel under the cursor. Stepped: one scroll position per captured
     * frame on a fixed 30fps virtual clock, so scrolls are perfectly smooth however slow capture is.
     */
    async scroll(dy, { ms = 1400 } = {}) {
      const t0 = now();
      const el = await page.evaluateHandle(([cx, cy]) => {
        let n = document.elementFromPoint(cx, cy);
        const ok = (e) => { const o = getComputedStyle(e).overflowY; return e.scrollHeight > e.clientHeight + 4 && (o === 'auto' || o === 'scroll'); };
        while (n && n !== document.body && !ok(n)) n = n.parentElement;
        return !n || n === document.body ? document.scrollingElement : n;
      }, [cur.x, cur.y]);
      const start = await el.evaluate((e) => e.scrollTop);
      await stepFrames(ms, (k) => el.evaluate((e, y) => { e.scrollTop = y; }, start + dy * ease(k)));
      await el.dispose();
      log({ type: 'scroll', t0, t1: now(), dy });
    },
    /** Visible drag: mouse down on `from`, eased move to `to`, mouse up. Apps with HTML5 DnD may need custom code. */
    async drag(from, to, { ms = 1200 } = {}) {
      await r.move(from);
      log({ type: 'click', t: now(), x: cur.x, y: cur.y, box: await box(from) });
      await page.mouse.down();
      await r.move(to, { ms });
      await page.mouse.up();
    },
    wait: (ms) => sleep(ms),
    setCursor(x, y) { cur = { x, y }; return page.mouse.move(x, y); },
  };
  return r;
}

// Without these flags headless Chrome screencasts a cropped (~W x 0.9H) 1x image, which then gets stretched.
const browser = await chromium.launch({ channel: 'chrome', args: [`--window-size=${W},${H}`, `--force-device-scale-factor=${DPR}`] });
const authFile = path.join(dir, '.auth.json');
if (config.login) {
  const ctx = await browser.newContext({ viewport: { width: W, height: H } });
  const page = await ctx.newPage();
  await config.login(page, BASE);
  await ctx.storageState({ path: authFile });
  await ctx.close();
}

for (const [name, fn] of Object.entries(scenes)) {
  if (only.length && !only.includes(name)) continue;
  const ctx = await browser.newContext({ viewport: { width: W, height: H }, deviceScaleFactor: DPR, colorScheme: 'light', ...(config.login ? { storageState: authFile } : {}) });
  await ctx.addInitScript(INIT);
  const page = await ctx.newPage();
  page.setDefaultTimeout(60000);
  const frameDir = path.join(OUT, `${name}_frames`);
  fs.rmSync(frameDir, { recursive: true, force: true }); fs.mkdirSync(frameDir);
  const cdp = await ctx.newCDPSession(page);
  const frames = [];
  const writes = [];
  clock = { offset: 0, stepping: false, frames, cdp };
  clock.push = (data, t) => {
    if (frames.length && t <= frames.at(-1).t) return; // stale in-flight frame
    const f = path.join(frameDir, String(frames.length).padStart(5, '0') + '.jpg');
    frames.push({ f, t });
    writes.push(fs.promises.writeFile(f, Buffer.from(data, 'base64')));
  };
  cdp.on('Page.screencastFrame', ({ data, metadata, sessionId }) => {
    // Ack first: Chrome waits for the ack before producing the next frame, so any delay here drops frames.
    cdp.send('Page.screencastFrameAck', { sessionId }).catch(() => {});
    if (clock.stepping) return; // stepped scroll takes its own screenshots (see r.scroll)
    clock.push(data, metadata.timestamp - clock.offset);
  });
  await cdp.send('Page.startScreencast', { format: 'jpeg', quality: config.quality ?? 85, maxWidth: W * DPR, maxHeight: H * DPR, everyNthFrame: 1 });
  const r = recorder(page);
  await r.setCursor(W * 0.62, H * 0.3);
  try { await fn(r); } catch (e) { console.error(`✗ ${name}: ${e.message}`); }
  await sleep(400);
  await cdp.send('Page.stopScreencast');
  const tEnd = now();
  await Promise.all(writes);
  await ctx.close();
  if (!r.t0) { console.error(`✗ ${name}: scene never called r.goto()`); continue; }
  let kept = frames.filter((fr) => fr.t >= r.t0 - 0.05);
  if (!kept.length) kept = frames.slice(-1);
  const t0 = kept[0].t;
  // Exact 30fps resample: output frame n shows the captured frame nearest to n/30s.
  // (ffmpeg's concat demuxer rounds image timestamps to 25fps, which duplicated every 6th frame.)
  const seq = path.join(frameDir, 'seq');
  fs.mkdirSync(seq);
  const total = Math.max(1, Math.round((tEnd - t0) * 30));
  for (let n = 0, j = 0; n < total; n++) {
    const target = t0 + n / 30;
    while (j < kept.length - 1 && Math.abs(kept[j + 1].t - target) <= Math.abs(kept[j].t - target)) j++;
    fs.symlinkSync(kept[j].f, path.join(seq, String(n).padStart(6, '0') + '.jpg'));
  }
  const dest = path.join(OUT, `${name}.mp4`);
  execFileSync('ffmpeg', ['-y', '-loglevel', 'error', '-framerate', '30', '-i', path.join(seq, '%06d.jpg'), '-vf', `scale=${Math.round(W * DPR / 2) * 2}:${Math.round(H * DPR / 2) * 2}:flags=lanczos,format=yuv420p`, '-r', '30', '-c:v', 'libx264', '-crf', '16', '-preset', 'medium', dest]);
  const PX = ['x', 'y', 'x0', 'y0', 'dy'];
  const rel = (e) => Object.fromEntries(Object.entries(e).map(([k, v]) => [k, ['t', 't0', 't1'].includes(k) ? +(v - t0).toFixed(3) : PX.includes(k) ? Math.round(v) : v]));
  const duration = +(tEnd - t0).toFixed(2);
  fs.writeFileSync(path.join(OUT, `${name}.events.json`), JSON.stringify({ size: [W, H], duration, events: r.events.map(rel) }, null, 1));
  fs.rmSync(frameDir, { recursive: true, force: true });
  console.log(`✓ ${name}  ${duration}s  ${kept.length} frames (${(kept.length / duration).toFixed(0)} fps)  ${r.events.length} events`);
}
await browser.close();
