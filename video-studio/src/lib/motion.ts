import { Easing } from 'remotion';
import type { Box, CamKey, RecEvent } from '../types';

export const clamp = (v: number, lo = 0, hi = 1) => Math.min(hi, Math.max(lo, v));
export const lerp = (a: number, b: number, t: number) => a + (b - a) * t;
// Smooth "camera operator" curve: slow start, decisive middle, gentle landing.
export const camEase = Easing.bezier(0.65, 0, 0.25, 1);
export const outEase = Easing.bezier(0.16, 1, 0.3, 1);
// Transitions ease in AND out, so no scene starts or stops moving abruptly.
export const transitionEase = Easing.bezier(0.45, 0, 0.2, 1);
// For overlays fading in (highlights, dims): soft start, no one-frame pop.
export const fadeEase = Easing.bezier(0.33, 0, 0.2, 1);

export type CamState = { x: number; y: number; zoom: number };

/** Camera state at clip time `t` from keyframes. Each key starts moving at key.t and lands after key.dur. */
export function cameraAt(keys: CamKey[], t: number, size: [number, number]): CamState {
  const start: CamState = { x: size[0] / 2, y: size[1] / 2, zoom: 1 };
  const sorted = [...keys].sort((a, b) => a.t - b.t);
  type R = { t: number; dur: number; from: CamState; to: CamState };
  const res: R[] = [];
  for (const k of sorted) {
    const prev = res[res.length - 1];
    const from = prev ? mix(prev.from, prev.to, camEase(clamp((k.t - prev.t) / prev.dur))) : start;
    const base = prev ? prev.to : start;
    res.push({ t: k.t, dur: Math.max(0.05, k.dur ?? 0.9), from, to: { x: k.x ?? base.x, y: k.y ?? base.y, zoom: k.zoom } });
  }
  let cur: R | undefined;
  for (const r of res) if (r.t <= t) cur = r;
  if (!cur) return start;
  return mix(cur.from, cur.to, camEase(clamp((t - cur.t) / cur.dur)));
}

function mix(a: CamState, b: CamState, p: number): CamState {
  // Zoom in log space so zooming in and out feel equally fast.
  return { x: lerp(a.x, b.x, p), y: lerp(a.y, b.y, p), zoom: Math.exp(lerp(Math.log(a.zoom), Math.log(b.zoom), p)) };
}

/** Builds camera keyframes from recorded interaction events. */
export function autoCamera(events: RecEvent[], size: [number, number], opts: { maxZoom?: number } = {}): CamKey[] {
  const maxZoom = opts.maxZoom ?? 1.8;
  const [W, H] = size;
  type Target = { t: number; x: number; y: number; zoom: number; hold: number };
  const targets: Target[] = [];
  const zoomFor = (b: Box | undefined, wanted?: number) => {
    if (wanted) return wanted;
    if (!b) return maxZoom;
    // Keep the element comfortably inside the frame: it should use at most ~55% of the visible width.
    const fit = Math.min((W * 0.55) / Math.max(b.w, 1), (H * 0.55) / Math.max(b.h, 1));
    return clamp(fit, 1.25, maxZoom);
  };
  for (const e of events) {
    if (e.type === 'click' && e.zoom !== false) {
      const b = e.box;
      targets.push({ t: e.t, x: b ? b.x + b.w / 2 : e.x, y: b ? b.y + b.h / 2 : e.y, zoom: zoomFor(b, e.zoom || undefined), hold: 1.1 });
    } else if (e.type === 'focus') {
      targets.push({ t: e.t, x: e.box.x + e.box.w / 2, y: e.box.y + e.box.h / 2, zoom: zoomFor(e.box, e.zoom), hold: e.hold ?? 1.4 });
    } else if (e.type === 'type') {
      targets.push({ t: e.t0, x: e.box.x + e.box.w / 2, y: e.box.y + e.box.h / 2, zoom: zoomFor(e.box), hold: e.t1 - e.t0 + 0.8 });
    } else if (e.type === 'highlight') {
      targets.push({ t: e.t, x: e.box.x + e.box.w / 2, y: e.box.y + e.box.h / 2, zoom: zoomFor(e.box), hold: e.dur });
    }
  }
  targets.sort((a, b) => a.t - b.t);
  const breaks = events.filter((e) => e.type === 'scroll' || e.type === 'nav').map((e) => (e.type === 'scroll' ? e.t0 : e.t));

  const keys: CamKey[] = [];
  const LEAD = 0.75; // arrive a little before the action
  let zoomedUntil = -1;
  let last: Target | undefined;
  for (const tg of targets) {
    const breakBetween = last && breaks.some((b) => b > last!.t && b < tg.t);
    const near = last && Math.abs(tg.x - last.x) < W / (2 * last.zoom) && Math.abs(tg.y - last.y) < H / (2 * last.zoom);
    if (last && zoomedUntil >= 0 && tg.t - LEAD > zoomedUntil + 0.2) {
      // Previous group finished: pull back out before the next one.
      keys.push({ t: zoomedUntil, zoom: 1, x: W / 2, y: H / 2, dur: 1.0 });
    } else if (last && breakBetween) {
      keys.push({ t: Math.max(last.t + 0.3, tg.t - LEAD - 1.0), zoom: 1, x: W / 2, y: H / 2, dur: 0.8 });
    }
    const continuing = last && zoomedUntil >= tg.t - LEAD && !breakBetween;
    keys.push({ t: Math.max(0, tg.t - LEAD), zoom: tg.zoom, x: tg.x, y: tg.y, dur: continuing && near ? 0.6 : 0.85 });
    zoomedUntil = tg.t + tg.hold;
    last = tg;
  }
  if (last) keys.push({ t: zoomedUntil, zoom: 1, x: W / 2, y: H / 2, dur: 1.0 });
  // During scrolls show the whole page.
  for (const e of events) if (e.type === 'scroll' && !keys.some((k) => Math.abs(k.t - e.t0) < 0.4)) keys.push({ t: Math.max(0, e.t0 - 0.3), zoom: 1, x: W / 2, y: H / 2, dur: 0.6 });
  return keys.sort((a, b) => a.t - b.t);
}

export type CursorState = { x: number; y: number; visible: boolean; pressed: number };

/** Cursor position at clip time t from move/click events (moves are eased, like the recorder). */
export function cursorAt(events: RecEvent[], t: number): CursorState {
  const moves = events.filter((e): e is Extract<RecEvent, { type: 'move' }> => e.type === 'move').sort((a, b) => a.t0 - b.t0);
  if (!moves.length) return { x: 0, y: 0, visible: false, pressed: 0 };
  let x = moves[0].x0, y = moves[0].y0;
  for (const m of moves) {
    if (t < m.t0) break;
    if (t >= m.t1) { x = m.x; y = m.y; continue; }
    const p = camEase(clamp((t - m.t0) / Math.max(0.01, m.t1 - m.t0)));
    x = lerp(m.x0, m.x, p); y = lerp(m.y0, m.y, p);
    break;
  }
  let pressed = 0;
  for (const e of events) if (e.type === 'click') { const d = t - e.t; if (d >= -0.06 && d < 0.22) pressed = 1 - Math.abs(d - 0.06) / 0.16; }
  return { x, y, visible: true, pressed: clamp(pressed) };
}
