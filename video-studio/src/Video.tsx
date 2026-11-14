import React from 'react';
import { AbsoluteFill, Audio, Sequence, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { TransitionSeries, linearTiming, type TransitionPresentation, type TransitionPresentationComponentProps } from '@remotion/transitions';
import { fade } from '@remotion/transitions/fade';
import { slide } from '@remotion/transitions/slide';
import type { Scene, Storyboard, TransitionName } from './types';
import { BrandProvider, FONT, Mark, useBrand } from './lib/brand';
import { ScreenScene } from './scenes/ScreenScene';
import { DiagramScene, EndScene, FeaturesScene, StatementScene, TitleScene } from './scenes/CardScenes';
import { camEase, clamp, transitionEase } from './lib/motion';

// ---------- timeline ----------
export function sceneSeconds(s: Scene): number {
  let base: number;
  if (s.type === 'screen') {
    const end = s.trim?.[1] ?? s._events?.duration ?? 6;
    base = (end - (s.trim?.[0] ?? 0)) / (s.rate ?? 1);
  } else base = s.duration;
  return s.voDuration ? Math.max(base, s.voDuration + 0.9) : base;
}

export function timeline(sb: Storyboard) {
  const fps = sb.fps ?? 30;
  const tr = Math.round((sb.transitionDuration ?? 0.6) * fps);
  let at = 0;
  const items = sb.scenes.map((s, i) => {
    const frames = Math.round(sceneSeconds(s) * fps);
    const name: TransitionName = i < sb.scenes.length - 1 ? s.transition ?? sb.transition ?? 'fade' : 'none';
    const trFrames = name === 'none' ? 0 : Math.min(tr, Math.floor(frames / 2));
    const item = { scene: s, from: at, frames, trFrames, transition: name };
    at += frames - trFrames;
    return item;
  });
  const last = items[items.length - 1];
  return { fps, items, total: last ? last.from + last.frames : 1 };
}

// ---------- custom "camera push" transition ----------
const Push: React.FC<TransitionPresentationComponentProps<Record<string, never>>> = ({ children, presentationDirection, presentationProgress }) => {
  const p = camEase(presentationProgress);
  const style: React.CSSProperties = presentationDirection === 'exiting'
    ? { transform: `scale(${1 + 0.12 * p})`, opacity: 1 - p, filter: `blur(${p * 10}px)` }
    : { transform: `scale(${0.9 + 0.1 * p})`, opacity: p, filter: `blur(${(1 - p) * 10}px)` };
  return <AbsoluteFill style={style}>{children}</AbsoluteFill>;
};
const push = (): TransitionPresentation<Record<string, never>> => ({ component: Push, props: {} });

function presentation(name: TransitionName): TransitionPresentation<any> {
  if (name === 'slide') return slide({ direction: 'from-right' });
  if (name === 'zoom') return push();
  return fade();
}

const SceneView: React.FC<{ scene: Scene; frames: number }> = ({ scene, frames }) => {
  switch (scene.type) {
    case 'screen': return <ScreenScene scene={scene} durationInFrames={frames} />;
    case 'title': return <TitleScene scene={scene} />;
    case 'statement': return <StatementScene scene={scene} />;
    case 'features': return <FeaturesScene scene={scene} durationInFrames={frames} />;
    case 'diagram': return <DiagramScene scene={scene} durationInFrames={frames} />;
    case 'end': return <EndScene scene={scene} />;
  }
};

// ---------- overlays ----------
const ChapterChip: React.FC<{ tl: ReturnType<typeof timeline> }> = ({ tl }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const idx = tl.items.findIndex((it) => f >= it.from && f < it.from + it.frames - it.trFrames / 2);
  const cur = tl.items[idx === -1 ? tl.items.length - 1 : idx];
  if (!cur?.scene.chapter) return null;
  // Spring in whenever the chapter text changes.
  let start = cur.from;
  for (let i = tl.items.indexOf(cur) - 1; i >= 0 && tl.items[i].scene.chapter === cur.scene.chapter; i--) start = tl.items[i].from;
  const s = spring({ frame: f - start - 6, fps, config: { damping: 18, stiffness: 140 } });
  return (
    <div style={{ position: 'absolute', left: 44, top: 38, display: 'flex', alignItems: 'center', gap: 12, padding: '10px 20px 10px 10px', borderRadius: 999, background: 'rgba(255,255,255,.92)',
      boxShadow: '0 10px 30px rgba(40,30,130,.16), 0 0 0 1px rgba(40,30,130,.08)', fontFamily: FONT, fontSize: 22, fontWeight: 650 as unknown as number, color: b.ink, opacity: s, transform: `translateX(${(1 - s) * -30}px)` }}>
      <Mark size={34} />
      <span style={{ opacity: 0.6 }}>{b.product ?? b.name}</span>
      <span style={{ width: 1, height: 20, background: 'rgba(30,27,75,.18)' }} />
      <span style={{ color: b.primaryDark }}>{cur.scene.chapter}</span>
    </div>
  );
};

const StepTracker: React.FC<{ tl: ReturnType<typeof timeline> }> = ({ tl }) => {
  const f = useCurrentFrame();
  const b = useBrand();
  const cur = tl.items.find((it) => f >= it.from && f < it.from + it.frames);
  const st = cur?.scene.step;
  if (!cur || !st) return null;
  const inP = clamp((f - cur.from) / 12);
  return (
    <div style={{ position: 'absolute', top: 40, left: 0, right: 0, display: 'flex', justifyContent: 'center', opacity: inP }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '10px 22px', borderRadius: 999, background: 'rgba(255,255,255,.94)', boxShadow: '0 10px 30px rgba(40,30,130,.16)', fontFamily: FONT, fontSize: 22, fontWeight: 650 as unknown as number, color: b.ink }}>
        {Array.from({ length: st.total }, (_, i) => (
          <React.Fragment key={i}>
            <span style={{ width: 34, height: 34, borderRadius: 17, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18,
              background: i + 1 <= st.n ? b.primary : '#ecebf7', color: i + 1 <= st.n ? '#fff' : '#8a8aa3', transform: i + 1 === st.n ? `scale(${1 + 0.12 * Math.sin(Math.min(1, (f - cur.from) / 14) * Math.PI)})` : undefined }}>{i + 1}</span>
            {i < st.total - 1 && <span style={{ width: 36, height: 3, borderRadius: 2, background: i + 1 < st.n ? b.primary : '#e3e2f0' }} />}
          </React.Fragment>
        ))}
        {st.label && <span style={{ marginLeft: 8 }}>{st.label}</span>}
      </div>
    </div>
  );
};

const Captions: React.FC<{ tl: ReturnType<typeof timeline> }> = ({ tl }) => {
  const f = useCurrentFrame();
  const cur = tl.items.find((it) => f >= it.from + 8 && f < it.from + it.frames - it.trFrames);
  if (!cur?.scene.caption) return null;
  const o = Math.min(clamp((f - cur.from - 8) / 8), clamp((cur.from + cur.frames - cur.trFrames - f) / 8));
  return (
    <div style={{ position: 'absolute', bottom: 54, left: 0, right: 0, display: 'flex', justifyContent: 'center', opacity: o }}>
      <div style={{ maxWidth: 1400, fontFamily: FONT, fontSize: 34, fontWeight: 600, color: '#fff', background: 'rgba(18,14,60,.82)', padding: '14px 28px', borderRadius: 16, textAlign: 'center', lineHeight: 1.3 }}>{cur.scene.caption}</div>
    </div>
  );
};

// ---------- composition ----------
export const ProductVideo: React.FC<Storyboard> = (sb) => {
  const tl = timeline(sb);
  const voiced = tl.items.filter((it) => it.scene.voFile).map((it) => [it.from + 9, it.from + 9 + Math.round((it.scene.voDuration ?? 0) * tl.fps)] as const);
  const base = sb.musicVolume ?? 0.5; // music is normalised to -16 LUFS, so 0.5 = ~-22 LUFS under/between voice
  const musicVolume = (f: number) => {
    const fadeIn = interpolate(f, [0, 30], [0, 1], { extrapolateRight: 'clamp' });
    const fadeOut = interpolate(f, [tl.total - 75, tl.total], [1, 0], { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' });
    // Duck under voice with 0.4s ramps.
    let duck = 0;
    for (const [a, z] of voiced) duck = Math.max(duck, Math.min(clamp((f - a + 12) / 12), clamp((z + 12 - f) / 12)));
    return base * fadeIn * fadeOut * (1 - 0.65 * duck);
  };
  return (
    <BrandProvider brand={sb.brand}>
      <AbsoluteFill style={{ background: '#f3f2ff' }}>
        <TransitionSeries>
          {tl.items.flatMap((it, i) => {
            const nodes = [
              <TransitionSeries.Sequence key={`s${i}`} durationInFrames={it.frames}>
                <SceneView scene={it.scene} frames={it.frames} />
                {it.scene.voFile && <Sequence from={9}><Audio src={staticFile(it.scene.voFile)} /></Sequence>}
              </TransitionSeries.Sequence>,
            ];
            if (it.trFrames > 0) nodes.push(<TransitionSeries.Transition key={`t${i}`} presentation={presentation(it.transition)} timing={linearTiming({ durationInFrames: it.trFrames, easing: transitionEase })} />);
            return nodes;
          })}
        </TransitionSeries>
        <ChapterChip tl={tl} />
        <StepTracker tl={tl} />
        <Captions tl={tl} />
        {sb.music && <Audio src={staticFile(sb.music)} volume={musicVolume} loop />}
      </AbsoluteFill>
    </BrandProvider>
  );
};
