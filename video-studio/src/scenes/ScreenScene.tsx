import React, { useMemo } from 'react';
import { AbsoluteFill, Freeze, OffthreadVideo, staticFile, useCurrentFrame, useVideoConfig, spring, interpolate } from 'remotion';
import type { RecEvent, ScreenScene as S } from '../types';
import { autoCamera, cameraAt, clamp, cursorAt, fadeEase, lerp, outEase } from '../lib/motion';
import { Backdrop, Kinetic, Rise } from '../components/Primitives';
import { FONT, useBrand } from '../lib/brand';

const STAGE_W = 1920, STAGE_H = 1080, CHROME = 46;

export function windowRect(scene: S) {
  const [W, H] = scene.size;
  const aspect = H / W;
  if (scene.layout === 'split-left' || scene.layout === 'split-right') {
    const w = 1110, h = w * aspect + CHROME;
    return { x: scene.layout === 'split-left' ? STAGE_W - w - 64 : 64, y: (STAGE_H - h) / 2, w, h };
  }
  const pad = 56;
  const w = Math.min(STAGE_W - 2 * pad, (STAGE_H - 2 * pad - CHROME) / aspect);
  const h = w * aspect + CHROME;
  return { x: (STAGE_W - w) / 2, y: (STAGE_H - h) / 2, w, h };
}

export const ScreenScene: React.FC<{ scene: S; durationInFrames: number }> = ({ scene, durationInFrames }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const [W, H] = scene.size;
  const rate = scene.rate ?? 1;
  const trim0 = scene.trim?.[0] ?? 0;
  const trim1 = scene.trim?.[1] ?? scene._events?.duration ?? Infinity;
  // If the voice runs longer than the footage, hold the last frame (camera/drift keep moving).
  const lastFrame = Math.floor(((trim1 - trim0) / rate) * fps) - 1;
  const t = trim0 + (Math.min(f, lastFrame) / fps) * rate; // clip time
  const events: RecEvent[] = scene._events?.events ?? [];
  const R = windowRect(scene);
  const s = R.w / W; // clip px -> stage px

  const keys = useMemo(() => {
    if (scene.cameraMode === 'static') return [];
    if (scene.camera?.length) return scene.camera;
    return autoCamera(events, scene.size, { maxZoom: scene.maxZoom });
  }, [scene, events]);

  const cam = cameraAt(keys, t, scene.size);
  const progress = f / Math.max(1, durationInFrames);
  const z = cam.zoom * (1 + (scene.drift ?? 0.035) * progress);
  const split = scene.layout === 'split-left' || scene.layout === 'split-right';
  const axis = (stage: number, focus: number, r0: number, r1: number, zz: number) => {
    const a = stage - r1 * zz, c = -r0 * zz;
    return clamp(stage / 2 - focus * zz, Math.min(a, c), Math.max(a, c));
  };
  // Near zoom 1 keep things centred; the focus point takes over as we zoom in.
  const w = clamp((z - 1) / 0.35);
  const contentH = R.w * (H / W);
  // Full layout: the whole window is the camera subject (background disappears as we zoom).
  // Split layout: the window stays put beside the headline and the camera zooms *inside* it.
  let stageT = { x: 0, y: 0, z: 1 }, innerT = { x: 0, y: 0, z: 1 };
  if (split) {
    const fx = lerp(R.w / 2, cam.x * s, w), fy = lerp(contentH / 2, cam.y * s, w);
    innerT = { x: axis(R.w, fx, 0, R.w, z), y: axis(contentH, fy, 0, contentH, z), z };
  } else {
    const fx = lerp(R.x + R.w / 2, R.x + cam.x * s, w);
    const fy = lerp(R.y + R.h / 2, R.y + CHROME + cam.y * s, w);
    stageT = { x: axis(STAGE_W, fx, R.x, R.x + R.w, z), y: axis(STAGE_H, fy, R.y, R.y + R.h, z), z };
  }

  const enter = spring({ frame: f, fps, config: { damping: 22, stiffness: 110 } });
  const shadow = interpolate(stageT.z, [1, 1.3], [1, 0.4], { extrapolateRight: 'clamp' });
  const cur = scene.cursor === false ? null : cursorAt(events, t);
  const highlights = [
    ...(scene.highlights ?? []),
    ...events.filter((e): e is Extract<RecEvent, { type: 'highlight' }> => e.type === 'highlight').map((e) => ({ t: e.t, dur: e.dur, box: e.box, label: e.label, spotlight: true })),
  ];

  return (
    <AbsoluteFill style={{ overflow: 'hidden' }}>
      <Backdrop />
      <AbsoluteFill style={{ transformOrigin: '0 0', transform: `translate(${stageT.x}px, ${stageT.y}px) scale(${stageT.z})` }}>
        <div style={{ position: 'absolute', left: R.x, top: R.y, width: R.w, height: R.h, borderRadius: 18, overflow: 'hidden', background: '#fff',
          boxShadow: `0 ${40 * shadow}px ${90 * shadow}px rgba(30,20,110,${0.28 * shadow}), 0 0 0 1px rgba(30,20,110,.08)`,
          opacity: enter, transform: `translateY(${(1 - enter) * 40}px) scale(${0.96 + 0.04 * enter})` }}>
          <Chrome url={scene.url} />
          <div style={{ position: 'relative', width: R.w, height: contentH, overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, transformOrigin: '0 0', transform: `translate(${innerT.x}px, ${innerT.y}px) scale(${innerT.z})` }}>
            <Freeze frame={lastFrame} active={f > lastFrame}>
              <OffthreadVideo src={staticFile(scene.src)} muted trimBefore={Math.round(trim0 * fps)} playbackRate={rate} style={{ width: '100%', height: '100%', display: 'block' }} />
            </Freeze>
            <div style={{ position: 'absolute', inset: 0, transformOrigin: '0 0', transform: `scale(${s})`, width: W, height: H }}>
              {highlights.map((h, i) => <HighlightBox key={i} now={t} start={h.t} dur={h.dur} box={h.box} label={h.label} spotlight={h.spotlight} />)}
              {events.filter((e): e is Extract<RecEvent, { type: 'click' }> => e.type === 'click').map((e, i) => <Ripple key={i} x={e.x} y={e.y} age={t - e.t} color={b.primary} />)}
              {cur?.visible && <Cursor x={cur.x} y={cur.y} pressed={cur.pressed} zoom={z} />}
            </div>
          </div>
          </div>
        </div>
      </AbsoluteFill>
      {split && scene.headline && (
        <div style={{ position: 'absolute', top: 0, bottom: 0, width: 600, display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: 26,
          ...(scene.layout === 'split-left' ? { left: 96 } : { right: 96 }) }}>
          {scene.headline.kicker && <Rise delay={4}><div style={{ fontFamily: FONT, fontSize: 26, fontWeight: 700, letterSpacing: 3, textTransform: 'uppercase', color: b.primary }}>{scene.headline.kicker}</div></Rise>}
          <Kinetic text={scene.headline.title} size={70} delay={8} />
          {scene.headline.body && <Rise delay={22}><div style={{ fontFamily: FONT, fontSize: 29, lineHeight: 1.4, color: '#4b4f6b', fontWeight: 500 }}>{scene.headline.body}</div></Rise>}
        </div>
      )}
    </AbsoluteFill>
  );
};

const Chrome: React.FC<{ url?: string }> = ({ url }) => (
  <div style={{ height: CHROME, display: 'flex', alignItems: 'center', gap: 9, padding: '0 18px', background: '#f6f6fb', borderBottom: '1px solid #e7e6f2' }}>
    {['#ff5f57', '#febc2e', '#28c840'].map((c) => <span key={c} style={{ width: 13, height: 13, borderRadius: 7, background: c }} />)}
    <div style={{ marginLeft: 18, flex: 1, maxWidth: 620, height: 28, borderRadius: 8, background: '#fff', boxShadow: 'inset 0 0 0 1px #e4e3ef', fontFamily: FONT, fontSize: 15, color: '#6b6f86', display: 'flex', alignItems: 'center', padding: '0 14px' }}>
      {url ?? 'app.laradashboard.com'}
    </div>
  </div>
);

const Cursor: React.FC<{ x: number; y: number; pressed: number; zoom: number }> = ({ x, y, pressed, zoom }) => {
  const k = (1.25 / Math.sqrt(zoom)) * (1 - 0.18 * pressed);
  return (
    <svg width={30} height={30} viewBox="0 0 24 24" style={{ position: 'absolute', left: x, top: y, transformOrigin: '3px 2px', transform: `translate(-3px,-2px) scale(${k})`, filter: 'drop-shadow(0 3px 4px rgba(0,0,0,.35))', overflow: 'visible' }}>
      <path d="M4 2l16 9.5-7 1.6L9.5 21z" fill="#111" stroke="#fff" strokeWidth="1.7" strokeLinejoin="round" />
    </svg>
  );
};

const Ripple: React.FC<{ x: number; y: number; age: number; color: string }> = ({ x, y, age, color }) => {
  if (age < 0 || age > 0.7) return null;
  const p = outEase(age / 0.7);
  const r = 14 + 46 * p;
  return <div style={{ position: 'absolute', left: x - r, top: y - r, width: r * 2, height: r * 2, borderRadius: '50%', border: `3px solid ${color}`, background: `${color}22`, opacity: 1 - p }} />;
};

const HighlightBox: React.FC<{ now: number; start: number; dur: number; box: { x: number; y: number; w: number; h: number }; label?: string; spotlight?: boolean }> = ({ now, start, dur, box, label, spotlight }) => {
  const b = useBrand();
  const age = now - start;
  if (age < 0 || age > dur + 0.45) return null;
  const inP = fadeEase(clamp(age / 0.55));
  const outP = fadeEase(clamp((age - dur) / 0.45));
  const o = inP * (1 - outP);
  const pad = 8, sc = 1.12 - 0.12 * inP;
  return (
    <>
      {spotlight && <div style={{ position: 'absolute', left: box.x - pad, top: box.y - pad, width: box.w + pad * 2, height: box.h + pad * 2, borderRadius: 14, boxShadow: `0 0 0 4000px rgba(20,14,70,${0.38 * o})` }} />}
      <div style={{ position: 'absolute', left: box.x - pad, top: box.y - pad, width: box.w + pad * 2, height: box.h + pad * 2, borderRadius: 14, opacity: o,
        border: `3px solid ${b.primary}`, boxShadow: `0 0 0 6px ${b.primary}33, 0 10px 30px ${b.primary}44`, transform: `scale(${sc})` }} />
      {label && (
        <div style={{ position: 'absolute', left: box.x - pad, top: box.y + box.h + pad + 12, opacity: o, transform: `translateY(${(1 - inP) * 12}px)`,
          fontFamily: FONT, fontSize: 18, fontWeight: 700, color: '#fff', background: b.primary, padding: '8px 14px', borderRadius: 10, boxShadow: '0 8px 20px rgba(40,30,140,.3)', whiteSpace: 'nowrap' }}>
          {label}
        </div>
      )}
    </>
  );
};
