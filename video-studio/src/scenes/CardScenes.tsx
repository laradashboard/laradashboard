import React from 'react';
import { AbsoluteFill, Img, OffthreadVideo, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import type { DiagramScene as D, EndScene as E, FeaturesScene as F, StatementScene as St, TitleScene as T } from '../types';
import { Backdrop, Kinetic, Pill, Rise } from '../components/Primitives';
import { Icon } from '../components/Icon';
import { FONT, Mark, useBrand } from '../lib/brand';
import { clamp } from '../lib/motion';

const isVideo = (src: string) => /\.(mp4|webm|mov)$/i.test(src);

const HeroWindow: React.FC<{ src: string; at?: number; style: React.CSSProperties }> = ({ src, at = 0, style }) => {
  const { fps } = useVideoConfig();
  return (
    <div style={{ position: 'absolute', borderRadius: 16, overflow: 'hidden', background: '#fff', boxShadow: '0 40px 80px rgba(30,20,110,.30), 0 0 0 1px rgba(30,20,110,.08)', ...style }}>
      <div style={{ height: 30, background: '#f6f6fb', display: 'flex', gap: 7, alignItems: 'center', padding: '0 12px' }}>
        {['#ff5f57', '#febc2e', '#28c840'].map((c) => <span key={c} style={{ width: 10, height: 10, borderRadius: 5, background: c }} />)}
      </div>
      {isVideo(src)
        ? <OffthreadVideo src={staticFile(src)} muted trimBefore={Math.round(at * fps)} style={{ width: '100%', display: 'block' }} />
        : <Img src={staticFile(src)} style={{ width: '100%', display: 'block' }} />}
    </div>
  );
};

export const TitleScene: React.FC<{ scene: T }> = ({ scene }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const dark = !!scene.dark;
  const ink = dark ? '#fff' : b.ink;
  const hero = scene.hero ?? [];
  const bigS = spring({ frame: f - 10, fps, config: { damping: 14, stiffness: 90, mass: 0.9 } });
  const drift = f / 30;

  if (scene.big) {
    return (
      <AbsoluteFill style={{ alignItems: 'center', justifyContent: 'center' }}>
        <Backdrop dark={dark} />
        {hero.slice(0, 2).map((h, i) => {
          const s = spring({ frame: f - 18 - i * 6, fps, config: { damping: 20, stiffness: 60 } });
          const side = i === 0 ? -1 : 1;
          return <HeroWindow key={i} src={h.src} at={h.at} style={{ width: 760, top: 300 + 40 * i, left: side < 0 ? -140 : 1300, opacity: 0.22 * s,
            transform: `perspective(1600px) rotateY(${side * -22}deg) translateX(${side * (1 - s) * 200 + side * -drift * 4}px)`, filter: 'blur(1.5px)' }} />;
        })}
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 28, zIndex: 1 }}>
          <Rise delay={0}><div style={{ display: 'flex', alignItems: 'center', gap: 18, fontFamily: FONT, fontSize: 40, fontWeight: 700, color: ink }}><Mark size={58} />{b.name}{b.product ? <span style={{ fontWeight: 500, opacity: 0.7 }}>{b.product}</span> : null}</div></Rise>
          <div style={{ fontFamily: FONT, fontSize: 300, fontWeight: 800, letterSpacing: -12, lineHeight: 1, color: ink, opacity: bigS, transform: `scale(${0.7 + 0.3 * bigS})`, filter: `blur(${(1 - bigS) * 14}px)`,
            background: dark ? undefined : `linear-gradient(180deg, ${b.ink}, ${b.primary})`, WebkitBackgroundClip: dark ? undefined : 'text', WebkitTextFillColor: dark ? undefined : 'transparent' }}>
            {scene.big}
          </div>
          {scene.title && <Kinetic text={scene.title} size={56} delay={24} align="center" color={ink} weight={700} />}
          <div style={{ display: 'flex', gap: 16, marginTop: 10 }}>
            {(scene.chips ?? []).map((c, i) => <Rise key={c} delay={34 + i * 5}><Pill dark={dark}>{c}</Pill></Rise>)}
          </div>
        </div>
      </AbsoluteFill>
    );
  }

  return (
    <AbsoluteFill>
      <Backdrop dark={dark} />
      {hero.slice(0, 3).map((h, i) => {
        const s = spring({ frame: f - 12 - i * 7, fps, config: { damping: 20, stiffness: 70 } });
        return <HeroWindow key={i} src={h.src} at={h.at} style={{ width: 900, left: 980 + i * 70, top: 170 + i * 150, zIndex: 3 - i, opacity: s,
          transform: `perspective(1800px) rotateY(-24deg) rotateX(8deg) translateX(${(1 - s) * 300 - drift * (3 + i)}px) translateY(${Math.sin(drift / 2 + i) * 8}px)` }} />;
      })}
      <div style={{ position: 'absolute', left: 140, top: 0, bottom: 0, width: hero.length ? 860 : 1640, display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: 34 }}>
        <Rise><div style={{ display: 'flex', alignItems: 'center', gap: 16, fontFamily: FONT, fontSize: 32, fontWeight: 700, color: ink }}><Mark size={50} />{scene.kicker ?? `${b.name}${b.product ? ' ' + b.product : ''}`}</div></Rise>
        <Kinetic text={scene.title} size={hero.length ? 104 : 124} delay={8} color={ink} />
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 14 }}>
          {(scene.chips ?? []).map((c, i) => <Rise key={c} delay={26 + i * 4}><Pill dark={dark} style={{ fontSize: 24, padding: '11px 22px' }}>{c}</Pill></Rise>)}
        </div>
      </div>
    </AbsoluteFill>
  );
};

export const StatementScene: React.FC<{ scene: St }> = ({ scene }) => {
  const b = useBrand();
  return (
    <AbsoluteFill style={{ alignItems: 'center', justifyContent: 'center', padding: 160 }}>
      <Backdrop dark={scene.dark} />
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 36 }}>
        <Kinetic text={scene.text} size={118} align="center" color={scene.dark ? '#fff' : b.ink} stagger={4} />
        {scene.sub && <Rise delay={20}><div style={{ fontFamily: FONT, fontSize: 38, color: scene.dark ? 'rgba(255,255,255,.8)' : '#4b4f6b', fontWeight: 500, textAlign: 'center' }}>{scene.sub}</div></Rise>}
      </div>
    </AbsoluteFill>
  );
};

export const FeaturesScene: React.FC<{ scene: F; durationInFrames: number }> = ({ scene, durationInFrames }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const items = scene.items.slice(0, 6);
  const cols = items.length <= 4 ? 2 : 3;
  // A soft "spotlight" walks across the cards once they are in, so the frame never freezes.
  const walk = (f - 30 - items.length * 5) / ((durationInFrames - 40 - items.length * 5) / items.length);
  return (
    <AbsoluteFill style={{ alignItems: 'center', justifyContent: 'center' }}>
      <Backdrop />
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 56, width: cols === 3 ? 1560 : 1200 }}>
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 18 }}>
          {scene.kicker && <Rise><div style={{ fontFamily: FONT, fontSize: 24, fontWeight: 700, letterSpacing: 4, textTransform: 'uppercase', color: b.primary }}>{scene.kicker}</div></Rise>}
          <Kinetic text={scene.title} size={84} align="center" delay={4} />
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: `repeat(${cols}, 1fr)`, gap: 28, width: '100%' }}>
          {items.map((it, i) => {
            const s = spring({ frame: f - 16 - i * 5, fps, config: { damping: 17, stiffness: 120 } });
            const active = walk >= i && walk < i + 1 ? Math.sin(clamp(walk - i) * Math.PI) : 0;
            return (
              <div key={i} style={{ background: '#fff', borderRadius: 24, padding: '34px 34px 30px', display: 'flex', gap: 22, alignItems: 'flex-start',
                boxShadow: `0 ${18 + 14 * active}px ${44 + 20 * active}px rgba(40,30,130,${0.1 + 0.08 * active}), 0 0 0 ${1 + 2 * active}px ${active > 0.05 ? b.primary + '66' : 'rgba(40,30,130,.07)'}`,
                opacity: s, transform: `translateY(${(1 - s) * 60 - active * 8}px) scale(${0.92 + 0.08 * s})` }}>
                <div style={{ flex: 'none', width: 64, height: 64, borderRadius: 18, background: `${b.primary}14`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Icon name={it.icon} size={34} color={b.primary} />
                </div>
                <div>
                  <div style={{ fontFamily: FONT, fontSize: 32, fontWeight: 750 as unknown as number, color: b.ink, letterSpacing: -0.5 }}>{it.title}</div>
                  {it.desc && <div style={{ fontFamily: FONT, fontSize: 23, color: '#5b5f7a', marginTop: 8, lineHeight: 1.35 }}>{it.desc}</div>}
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </AbsoluteFill>
  );
};

export const DiagramScene: React.FC<{ scene: D; durationInFrames: number }> = ({ scene, durationInFrames }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const n = scene.nodes.length;
  const nodeW = 280, gap = n > 4 ? 90 : 150;
  const total = n * nodeW + (n - 1) * gap;
  const step = 16;
  return (
    <AbsoluteFill style={{ alignItems: 'center', justifyContent: 'center' }}>
      <Backdrop />
      {scene.title && <div style={{ position: 'absolute', top: 170, width: '100%', display: 'flex', justifyContent: 'center' }}><Kinetic text={scene.title} size={72} align="center" /></div>}
      <div style={{ position: 'relative', width: total, height: 300, marginTop: scene.title ? 120 : 0 }}>
        {scene.nodes.slice(0, -1).map((_, i) => {
          const p = clamp((f - 14 - i * step - 8) / 14);
          const x0 = i * (nodeW + gap) + nodeW, len = gap;
          const pulse = ((f - 14 - i * step) / 40) % 1;
          return (
            <div key={i} style={{ position: 'absolute', left: x0, top: 148, width: len, height: 4 }}>
              <div style={{ width: '100%', height: 4, borderRadius: 2, background: b.primary, transformOrigin: '0 50%', transform: `scaleX(${p})`, opacity: 0.85 }} />
              {p >= 1 && <div style={{ position: 'absolute', top: -5, left: `${pulse * 100}%`, width: 14, height: 14, marginLeft: -7, borderRadius: 7, background: '#fff', boxShadow: `0 0 0 4px ${b.primary}, 0 0 18px ${b.primary}` }} />}
            </div>
          );
        })}
        {scene.nodes.map((node, i) => {
          const s = spring({ frame: f - 6 - i * step, fps, config: { damping: 14, stiffness: 130 } });
          const checkS = scene.check === node.id ? spring({ frame: f - Math.round(durationInFrames * 0.6), fps, config: { damping: 10, stiffness: 160 } }) : 0;
          return (
            <div key={node.id} style={{ position: 'absolute', left: i * (nodeW + gap), top: 40, width: nodeW, height: 220, background: '#fff', borderRadius: 26,
              boxShadow: '0 24px 60px rgba(40,30,130,.14), 0 0 0 1px rgba(40,30,130,.07)', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 16,
              opacity: s, transform: `scale(${0.6 + 0.4 * s}) translateY(${Math.sin((f + i * 20) / 28) * 5}px)` }}>
              <div style={{ width: 76, height: 76, borderRadius: 22, background: i === 0 ? b.primary : `${b.primary}14`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <Icon name={node.icon ?? 'box'} size={40} color={i === 0 ? '#fff' : b.primary} />
              </div>
              <div style={{ fontFamily: FONT, fontSize: 28, fontWeight: 700, color: b.ink, textAlign: 'center', padding: '0 14px' }}>{node.label}</div>
              {node.sub && <div style={{ fontFamily: FONT, fontSize: 19, color: '#6b6f86', marginTop: -8 }}>{node.sub}</div>}
              {checkS > 0 && <div style={{ position: 'absolute', top: -18, right: -18, width: 54, height: 54, borderRadius: 27, background: '#16a34a', display: 'flex', alignItems: 'center', justifyContent: 'center', transform: `scale(${checkS})`, boxShadow: '0 8px 20px rgba(22,163,74,.4)' }}><Icon name="check" size={30} color="#fff" stroke={3} /></div>}
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

export const EndScene: React.FC<{ scene: E }> = ({ scene }) => {
  const f = useCurrentFrame();
  const { fps } = useVideoConfig();
  const b = useBrand();
  const cards = scene.cards ?? [];
  return (
    <AbsoluteFill style={{ alignItems: 'center', justifyContent: 'center' }}>
      <Backdrop />
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 54 }}>
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16 }}>
          <Rise><div style={{ fontFamily: FONT, fontSize: 24, fontWeight: 700, letterSpacing: 5, textTransform: 'uppercase', color: b.primary }}>{scene.kicker ?? 'Before you go'}</div></Rise>
          <Kinetic text={scene.title} size={96} align="center" delay={4} />
        </div>
        {cards.length > 0 && (
          <div style={{ display: 'flex', gap: 30 }}>
            {cards.map((c, i) => {
              const s = spring({ frame: f - 16 - i * 6, fps, config: { damping: 17, stiffness: 120 } });
              return (
                <div key={i} style={{ width: 440, background: '#fff', borderRadius: 26, padding: 36, boxShadow: '0 24px 60px rgba(40,30,130,.12), 0 0 0 1px rgba(40,30,130,.07)', opacity: s, transform: `translateY(${(1 - s) * 70}px)`, display: 'flex', flexDirection: 'column', gap: 18 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 14, fontFamily: FONT, fontSize: 34, fontWeight: 750 as unknown as number, color: b.ink }}>
                    <div style={{ width: 52, height: 52, borderRadius: 14, background: `${b.primary}14`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}><Icon name={c.icon ?? 'book'} size={28} color={b.primary} /></div>
                    {c.title}
                  </div>
                  {(c.lines ?? []).map((l, j) => <div key={j} style={{ fontFamily: FONT, fontSize: 23, color: '#4b4f6b', display: 'flex', gap: 10, alignItems: 'center' }}><Icon name="check" size={20} color={b.primary} stroke={2.6} />{l}</div>)}
                  {c.cta && <div style={{ marginTop: 8, fontFamily: FONT, fontSize: 20, fontWeight: 700, letterSpacing: 2, textTransform: 'uppercase', color: b.primaryDark, background: `${b.primary}12`, borderRadius: 12, padding: '14px 0', textAlign: 'center' }}>{c.cta}</div>}
                </div>
              );
            })}
          </div>
        )}
        {(scene.url ?? b.url) && (
          <Rise delay={30 + cards.length * 6}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, fontFamily: FONT, fontSize: 34, fontWeight: 700, color: '#fff', background: b.primary, padding: '18px 34px', borderRadius: 18, boxShadow: `0 18px 40px ${b.primary}55`,
              transform: `scale(${1 + 0.015 * Math.sin(f / 10)})` }}>
              <Mark size={44} bg="rgba(255,255,255,.18)" />{scene.url ?? b.url}
            </div>
          </Rise>
        )}
      </div>
    </AbsoluteFill>
  );
};

export const sceneFadeIn = (f: number) => interpolate(f, [0, 8], [0, 1], { extrapolateRight: 'clamp' });
